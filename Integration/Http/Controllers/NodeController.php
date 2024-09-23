<?php

namespace Modules\Integration\Http\Controllers;

use App\Traits\CacheBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Integration\Builders\Node\ApplicationBuilders\FactoryErrorBuilder;
use Modules\Integration\Events\ActionChanged;
use Modules\Integration\Exceptions\NodeBuildingException;
use Modules\Integration\Exceptions\NodeException;
use Modules\Integration\Services\TestIntegration;
use Modules\WebhooksApp\Entities\Webhook;
use Nwidart\Modules\Routing\Controller;
use Modules\Integration\Repositories\NodeRepository;
use Modules\Integration\Repositories\IntegrationRepository;
use Modules\Integration\Builders\NodeBuilder;
use Modules\Integration\Events\BeforeApplicationChange;
use Modules\Integration\Events\AfterApplicationChange;
use Modules\Application\Repositories\ApplicationRepository;
use Modules\User\Repositories\UserRepository;
use Modules\Billing\Helpers\DataTransfersHelper;

class NodeController extends Controller
{
    use CacheBuilder;

    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * @var IntegrationRepository
     */
    protected $integrationRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var ApplicationRepository
     */
    protected $applicationRepository;

    /**
     * @var NodeBuilder
     */
    protected $nodeBuilder;

    /**
     * NodeController constructor.
     *
     * @param NodeRepository $nodeRepository
     * @param IntegrationRepository $integrationRepository
     * @param UserRepository $userRepository,
     * @param NodeBuilder $nodeBuilder
     * @param ApplicationRepository $applicationRepository
     */
    public function __construct(
        NodeRepository $nodeRepository,
        IntegrationRepository $integrationRepository,
        UserRepository $userRepository,
        NodeBuilder $nodeBuilder,
        ApplicationRepository $applicationRepository
    )
    {
        $this->nodeRepository = $nodeRepository;
        $this->integrationRepository = $integrationRepository;
        $this->userRepository = $userRepository;
        $this->nodeBuilder = $nodeBuilder;
        $this->applicationRepository = $applicationRepository;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param string $integrationCode
     * @return Factory|View
     * @throws AuthorizationException
     */

    public function nodes(Request $request, string $integrationCode)
    {

        $integration = $this->integrationRepository->getByCode($integrationCode);

        // Allow watching for admin
        if(!authUserManager('admin')) {
            $this->authorize('manage', $integration);
        }
        else {
            if(is_null($integration))
                abort(404);
        }

        $nodes = $integration->nodes;

        $this->cacheRemember(['integration_'.$integration->id, 'applications'],
            function() use ($nodes) {
                return $nodes->load('application');
            }
        );

        if(!isset($nodes[0])) {
            $triggerNode = $this->nodeRepository->store([
                'integration_id' => $integration->getAttribute('id'),
                'ordering' => 1
            ]);
            $integration->active == true ? $this->integrationRepository->changeActive($integration, false) : null;
            $integration->flushCache();
        }
        else {
            $triggerNode = $nodes[0];
        }
        $triggerNodeApp = $triggerNode->application;

        if(!isset($nodes[0])) {
            $actionNode = $this->nodeRepository->store([
                'integration_id' => $integration->getAttribute('id'),
                'ordering' => 2
            ]);
            $integration->active == true ? $this->integrationRepository->changeActive($integration, false) : null;
            $integration->flushCache();
        }
        else {
            $actionNode = $nodes[1];
        }
        $actionNodeApp = $actionNode->application;

        if($integration->active == true && $request->get('action') == 'post_fields') {
            if($triggerNodeApp->type == 'webhooks_app') {
                $webhook = Webhook::query()->select('code')->where('integration_id', $integration->id)->first();
                $params = $request->except('action');
                Http::post(config('app.url').'/webhooks/catch/'.$webhook->code.'/webhooks-app', $params);
            }
        }

        $aDataTransfers = DataTransfersHelper::getDataTransfersByUser(auth()->user());

        return view('integration::nodes.nodes',
            compact('integration','nodes', 'triggerNodeApp', 'actionNodeApp', 'aDataTransfers'));
    }

    /**
     * @param string $integrationCode
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function nodesErrors(string $integrationCode)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $integrationCode);
            return response()->json(['message' => 'error'], 404);
        }

        $this->authorize('manage', $integration);

        if($integration->active == true)
            return response()->json([
                'alreadyActivated' => true
            ]);

        $triggerNode = $integration->triggerNode();
        if(!$triggerNode->application) {
            $triggerNodeErrors['critical'] = true;
        }
        else {
            $triggerErrorsBuilder = FactoryErrorBuilder::getInstance($triggerNode);
            $triggerErrorsBuilder->build();
            $triggerNodeErrors = $triggerErrorsBuilder->getErrors();
        }

        $actionNode = $integration->actionNode();
        if(!$actionNode->application) {
            $actionNodeErrors['critical'] = true;
        }
        else {
            $actionErrorsBuilder = FactoryErrorBuilder::getInstance($actionNode);
            $actionErrorsBuilder->build();
            $actionNodeErrors = $actionErrorsBuilder->getErrors();
        }

        $integrationTestErrors = false;

        $readyForActivation = empty($triggerNodeErrors) && empty($actionNodeErrors);
        if ($readyForActivation) {
            $testIntegrationService = new TestIntegration($triggerNode, $actionNode);
            $integrationTestErrors = !$testIntegrationService->execute();
        }

        $criticalError = isset($triggerNodeErrors['critical']) || isset($actionNodeErrors['critical']);

        $readyForActivation = empty($triggerNodeErrors) && empty($actionNodeErrors) && !$integrationTestErrors && empty($criticalError);

        return response()->json([
            'triggerNodeErrors' => $triggerNodeErrors,
            'actionNodeErrors' => $actionNodeErrors,
            'integrationTestErrors' => $integrationTestErrors,
            'readyForActivation' => $readyForActivation,
            'criticalError' => $criticalError
        ]);
    }


    /**
     * @param string $integrationCode
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws NodeBuildingException
     */
    public function nodesData(string $integrationCode)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);

        if(!authUserManager('admin'))
            $this->authorize('manage', $integration);

        $nodes = $integration->nodes;

        $node1 = $nodes[0];
        $this->nodeBuilder->setNodes($nodes);
        $this->nodeBuilder->build($node1);
        $triggerNode = $this->nodeBuilder->getNode();
        $triggerNodeArray = $this->nodeBuilder->getNodeArray();

        $node2 = $nodes[1];
        $this->nodeBuilder->setNodeModel($triggerNode); // set trigger node to action node builder
        $this->nodeBuilder->setNodes($nodes);
        $this->nodeBuilder->build($node2);
        $actionNodeArray = $this->nodeBuilder->getNodeArray();

        return response()->json([
            'integration' => $integration,
            'integrationName' => $integration->getTitle(),
            'triggerNode' => $triggerNodeArray,
            'actionNode' => $actionNodeArray
        ]);
    }

    /**
     * @param Request $request
     * @param string $integrationCode
     * @param int $nodeId
     * @return JsonResponse
     */
    public function saveApplication(Request $request, string $integrationCode, $nodeId)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);

        if(!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $integrationCode);
            return response()->json(['message' => 'error'], 404);
        }


        if($integration->active == true)
            return response()->json([
                'alreadyActivated' => true
            ]);

        $applicationId = $request->get('application_id');
        $userId = $request->get('user_id');
        $user = $this->userRepository->getById($userId);
        $node = $this->nodeRepository->getById($nodeId);

        $this->cacheFlushTags(['integrations', 'node_'.$nodeId, 'node_storage_'.$nodeId]);
        $this->cacheForget(['applications', 'applications_user_'.$userId]);

        if($node->isTrigger())
            $this->cacheFlushTags(['integrations_user_'.$userId]);

        $application =  $this->applicationRepository->getById($applicationId);
        if(!$application) {
            Log::channel('integrations')->warning('Application not found. Integration code:' . $integrationCode. '. Application id: '. $applicationId);
            return response()->json(['message' => 'error'], 404);
        }

        $nodeBeforeDataLoading = clone $node;

        // If user does not have this application, attach it
        if(!$user->hasApplication($applicationId)) {
            $this->userRepository->addApplication($user, $applicationId);
        }

        // Load application and integration by application id
        $node->setAttribute('application_id', $applicationId);
        $node->setAttribute('application_type', $application->type);
        $node->load('application');
        $node->load('integration');

        // Application account
        $applicationAccount = ($node->application) ? $node->application->account($userId) : null;

        // If application has account (in fact has settings) but settings are not completed
        // redirect to the settings page
        if($node->application->hasAccount() && !$applicationAccount) {
            return response()->json([
                'redirect' => url("/app/applications-attached/{$node->application->slug}/create")
            ]);
        }

        // Fire event before application change
        event(new BeforeApplicationChange($nodeBeforeDataLoading));

        // Update data of the base node
        $data = [
            'integration_id' => $node->integration_id,
            'application_id' => $applicationId,
            'application_type' => $application->type,
            'account_id' => ($applicationAccount) ? $applicationAccount->id : null,
            'ordering' => $node->ordering,
        ];
        $nodeUpdated = $this->nodeRepository->update($data, $node);

        // Fire event after application change
        event(new AfterApplicationChange($nodeUpdated));

        return response()->json([

        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $integrationCode
     * @return Factory|View
     * @throws AuthorizationException
     */
    public function create(string $integrationCode)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);
        $this->authorize('manage', $integration);

        return view(config('app.theme').'.front.nodes.create', compact('integration'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        $integrationCode = $request->input('integration_id');
        $integration = $this->integrationRepository->getByCode($integrationCode);
        $this->authorize('manage', $integration);

        $ordering = $this->nodeRepository->lastOrderingForIntegration($integration->id);
        $request->merge(['ordering' => $ordering+1]);
        $this->nodeRepository->store($request->all());

        return redirect('integrations/'.$request->input('integration_id').'/nodes')->with('status', __('integrations.node_stored'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $integrationCode
     * @param int $nodeId
     * @return Factory|View
     * @throws AuthorizationException
     */
    public function edit(string $integrationCode, int $nodeId)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);
        $this->authorize('manage', $integration);

        $node = $this->nodeRepository->getById($nodeId);
        return view(config('app.theme').'.front.nodes.edit', compact('integration', 'node'));
    }

    public function saveAction(Request $request, string $integrationCode, int $nodeId, string $slug)
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);

        if (!$integration) {
            Log::channel('integrations')->warning('Integration not found. Integration code:' . $integrationCode . '. Application slug: ' . $slug);
            return response()->json(['message' => 'error'], 404);
        }

        if ($integration->active == true)
            return response()->json([
                'alreadyActivated' => true
            ]);

        try {
            $application = \Modules\Application\Facades\ApplicationRepository::getBySlug($slug);
        } catch(\Exception $e) {
            Log::channel('integrations')->warning('Application not found. Integration code:' . $integrationCode . '. Application slug: ' . $slug);
            return response()->json(['message' => 'error'], 404);
        }

        $actionId = (int) $request->get('action_id');
        $baseNode = $this->nodeRepository->getById($nodeId);
        if(!$baseNode)
            return response()->json( [
                'message'    => __('integration::node.does_not_exist')
            ], 404);

        $this->cacheFlushTags(['node_'.$baseNode->id, 'node_storage_'.$nodeId]);
        if($baseNode->isTrigger())
            $this->cacheFlushTags(['node_'.$baseNode->nextNode()->id, 'node_storage_'.$nodeId]);

        // Add action
        try {
            $node = $baseNode->applicationNode;
        } catch (NodeException $e) {
            return response()->json([
                'applicationHasBeenChanged' => true
            ]);
        }
        $data = [
            'node_id' => $node->node_id,
            'action_id' => $actionId
        ];

        $appNodeRepository = null;
        try {
            $appNodeRepository = app()->make('Modules\\' . studly_case($application->type) . '\\Repositories\\NodeRepository');
        } catch (BindingResolutionException $e) {
            return response()->json(['Error!']);
        }

        try {
            $appNodeRepository->update($data, $node);
        } catch (\TypeError $e) {
            return response()->json(['applicationHasBeenChanged' => true]);
        }

        event(new ActionChanged($baseNode));
        return response()->json([

        ]);
    }

    /**
     * @throws NodeBuildingException
     * @throws AuthorizationException
     */
    public function clearNode(string $integrationCode, int $nodeId): JsonResponse
    {
        $integration = $this->integrationRepository->getByCode($integrationCode);
        $node = $this->nodeRepository->getById($nodeId);
        if(!$node) {
            return response()->json( [
                'message'    => __('integration::node.does_not_exist')
            ], 404);
        }

        $this->nodeRepository->deleteApplicationDependentData($node, true);
        if($node->isTrigger()) {
            $actionNode = $node->nextNode();
            $this->nodeRepository->deleteApplicationDependentData($actionNode, true);
        }
        $integration->flushCache();


        return $this->nodesData($integrationCode);
    }
}
