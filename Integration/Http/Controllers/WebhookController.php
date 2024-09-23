<?php

namespace Modules\Integration\Http\Controllers;

use App\Traits\CacheBuilder;
use Modules\Integration\Facades\NodeManagerFacade;
use Modules\Integration\Repositories\NodeRepository;
use Nwidart\Modules\Routing\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    use CacheBuilder;
    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * Controller constructor.
     *
     * @param NodeRepository $nodeRepository
     */
    public function __construct(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @param Request $request
     * @param string $webhookCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function openForSample(Request $request, string $webhookCode)
    {
        $nodeId = $request->get('node_id', 0);
        $appId = $request->get('application_id');

        $node = $this->nodeRepository->getById($nodeId);
        if(!$node)
            return response()->json( [
                'message'    => __('integration::node.does_not_exist')
            ], 404);

        if($node->application_id != $appId)
            return response()->json([
                'applicationHasBeenChanged' => true
            ]);

        $webhookRepository = NodeManagerFacade::load($node)->webhookRepository();
        $webhook = $webhookRepository->getByCode($webhookCode);
        if(!$webhook)
            return response()->json( [
                'message'    => __('integration::node.webhook_does_not_exist')
            ], 404);

        $integration = $webhook->integration;
        if($integration->active == true)
            return response()->json([
                'alreadyActivated' => true
            ]);

        $this->cacheForget(['node_'.$nodeId, 'webhook'], ['node_'.$nodeId, 'fields']);

        $nextNode = $node->nextNode();
        if(isset($nextNode))
            $this->cacheForget(['node_'.$nextNode, 'fields']);

        $webhookRepository->openGateForSample($webhook);

        return response()->json([
            'openedForSample' => true
        ]);

    }

    /**
     * @param Request $request
     * @param string $webhookCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function closeForSample(Request $request, string $webhookCode)
    {
        $nodeId = $request->get('node_id', 0);
        $appId = $request->get('application_id');
        $this->cacheForget(['node_'.$nodeId, 'webhook'], ['node_'.$nodeId, 'fields']);
        $node = $this->nodeRepository->getById($nodeId);
        if(!$node)
            return response()->json( [
                'message'    => __('integration::node.does_not_exist')
            ], 404);

        if($node->application_id != $appId)
            return response()->json([
                'applicationHasBeenChanged' => true
            ]);

        $nextNode = $node->nextNode();
        if(isset($nextNode))
            $this->cacheForget(['node_'.$nextNode, 'fields']);

        $webhookRepository = NodeManagerFacade::load($node)->webhookRepository();

        $webhook = $webhookRepository->getByCode($webhookCode);

        $webhookRepository->closeGateForSample($webhook);

        return response()->json([
            'openedForSample' => false
        ]);
    }

    /**
     * @param Request $request
     * @param string $webhookCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkGateAvailability(Request $request, string $webhookCode)
    {
        $nodeId = $request->get('node_id', 0);
        $appId = $request->get('application_id');
        $node = $this->nodeRepository->getById($nodeId);
        if(!$node)
            return response()->json( [
                'message'    => __('integration::node.does_not_exist')
            ], 404);

        if($node->application_id != $appId)
            return response()->json([
                'applicationHasBeenChanged' => true
            ]);

        $webhookRepository = NodeManagerFacade::load($node)->webhookRepository();

        $webhook = $webhookRepository->getByCode($webhookCode);
        if(!$webhook) {
            return response()->json( [
                'message'    => __('integration::node.webhook_does_not_exist')
            ], 404);
        }

        return response()->json([
            'openedForSample' => $webhook->opened_for_sample ? true : false
        ]);
    }
}
