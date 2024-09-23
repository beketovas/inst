<?php

namespace Modules\Instagram\Http\Controllers;

use Apiway\Hooks\HookFinder;
use Apiway\IncomingData\Contracts\IncomingDataRepository;
use App\Traits\CacheBuilder;
use Illuminate\Http\Request;
use Modules\Application\Facades\ApplicationRepository;
use Modules\Integration\Facades\IntegrationRepository;
use Modules\Integration\Facades\NodeManagerFacade;
use Modules\Integration\Http\Controllers\BaseWebhookController;
use Modules\Instagram\Exceptions\FieldServiceException;
use Modules\Instagram\Repositories\WebhookRepository;
use Modules\Instagram\Services\FieldService;
use Modules\Instagram\Repositories\NodeFieldRepository;
use Illuminate\Support\Facades\Log;
use Modules\Integration\Services\IncomingDataHandler;

class WebhookController extends BaseWebhookController
{
    use CacheBuilder;

    /**
     * @var WebhookRepository
     */
    protected WebhookRepository $webhookRepository;

    /**
     * @var IncomingDataRepository
     */
    protected IncomingDataRepository $incomingDataRepository;

    /**
     * @var FieldService
     */
    protected FieldService $fieldService;

    /**
     * @var NodeFieldRepository
     */
    protected NodeFieldRepository $fieldRepository;

    /**
     * WebhookController constructor.
     *
     * @param WebhookRepository $webhookRepository
     * @param IncomingDataRepository $incomingDataRepository
     * @param FieldService $fieldService
     * @param NodeFieldRepository $fieldRepository
     */
    public function __construct(
        WebhookRepository $webhookRepository,
        IncomingDataRepository $incomingDataRepository,
        FieldService $fieldService,
        NodeFieldRepository $fieldRepository)
    {
        parent::__construct();

        $this->webhookRepository = $webhookRepository;
        $this->incomingDataRepository = $incomingDataRepository;
        $this->fieldService = $fieldService;
        $this->fieldRepository = $fieldRepository;
    }



    protected function subscribe(Request $request)
    {
        $verification_phrase = config('instagram.service.verification_phrase');
        if ($request->input('hub_verify_token') == $verification_phrase) {
            return response($verification_phrase, 200);
        }
    }

    /**
     * @param Request $request
     * @param string $webhookCode
     * @return \Illuminate\Http\JsonResponse
     * @throws FieldServiceException
     */
    public function catchWebhook(Request $request, string $webhookCode)
    {
        if (
            $request->input('hub_mode') &&
            $request->input('hub_challenge') &&
            $request->input('hub_verify_token')
        ) {

            $verification_phrase = config('instagram.service.verification_phrase');
            if ($request->input('hub_verify_token') == $verification_phrase) {
                return response($request->input('hub_challenge'), 200);
            }
        }

        $hookFinder = new HookFinder($this->webhookRepository);
        $webhook = $hookFinder->find($webhookCode);

        if(!$webhook) {
            Log::channel('webhooks')->info("Instagram. Webhook ".$webhookCode." does not exist.");
            return response()->json([
                "status" => "failed"
            ], 404);
        }

        $webhookArray = json_decode(file_get_contents("php://input"), true);
        if(empty($webhookArray)) {
            $webhookArray = $request->all();
        }
        if(empty($webhookArray)) {
            Log::channel('webhooks')->info("Instagram. Webhook data for ".$webhook->id." is empty.");
            return response()->json([
                "status" => "failed"
            ], 404);
        }

        if(!is_array($webhookArray)) {
            Log::channel('webhooks')->info("Instagram. Webhook data for ".$webhook->id." must be an array in JSON format.");
            return response()->json([
                "status" => "failed"
            ], 404);
        }

        $integration = IntegrationRepository::getById($webhook->integration_id);

        // If webhook opened for sample, parse and save fields
        if($webhook->opened_for_sample) {
            $node = $webhook->node;
            $this->fieldService->setWebhookData($webhookArray);
            $this->fieldService->setNode($node);
            $dataStorage = $this->fieldService->prepareFields();
            if($dataStorage->getElements()->isNotEmpty()) {
                $actionNode = $integration->actionNode();
                if($actionNode->application_type) {
                    $actionNodeFieldRepository = NodeManagerFacade::load($actionNode)->fieldRepository('NodeField');
                    $actionNodeFieldRepository->deleteFieldsByFilter(['appNodeId' => $actionNode->applicationNode->id]);
                }
                $this->fieldRepository->deleteFieldsByFilter(['appNodeId' => $node->id]);
                $this->cacheForget(['node_'.$actionNode->id, 'fields']);
                $this->fieldRepository->saveDataElements($dataStorage->getElements(), $node->id);
                $this->webhookRepository->closeGateForSample($webhook);
            }

        } else { // if not, synchronize data
            if(!$integration->active) {
                Log::channel('webhooks')->info("Instagram. Integration ".$integration->id." is inactive to process webhook ".$webhook->id.".");
                return response()->json([
                    "status" => "Integration is inactive"
                ], 404);
            }

            $triggerNode = $integration->triggerNode();
            $application = ApplicationRepository::getByType($triggerNode->application_type);
            $data = [
                "app_type" => $triggerNode->application_type,
                "integration_id" => $integration->id,
                "webhook_data" => json_encode($webhookArray),
                "application_id" => $application->id,
                "processed" => 0
            ];

            $webhookData = $this->incomingDataRepository->saveNew($data);
            $incomingDataHandler = new IncomingDataHandler($integration, $webhook, $webhookData);;
            $incomingDataHandler->process();
        }


        return response()->json([
            "status" => "success 2"
        ]);
    }
}
