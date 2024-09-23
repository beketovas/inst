<?php

namespace Modules\Integration\Listeners;

use Illuminate\Contracts\Container\BindingResolutionException;
use Modules\Integration\Events\ActionChanged;
use Modules\Application\Contracts\NodeRepositoryContract;

class CleanActionDependentData
{

    protected $nodeRepository;

    /**
     * Handle the event.
     *
     * @param ActionChanged $event
     * @return void
     * @throws BindingResolutionException
     */
    public function handle(ActionChanged $event)
    {
		$node = $event->node;
		try {
            $nodeRepository = app()->make('Modules\\' . studly_case($node->application_type) . '\\Repositories\\NodeRepository');
		    if(!method_exists($nodeRepository, 'deleteActionDependentData'))
		        return;
            // Delete all dependent data from current node
            $nodeRepository->deleteActionDependentData($node);

            // If node is trigger, delete corresponding data from the action node
            if ($node->isTrigger()) {
                $actionNode = $node->nextNode();
                if (empty($actionNode->application)) return;
                $actionNodeRepository = app()->makeWith(NodeRepositoryContract::class, ['node' => $actionNode]);
                if(!method_exists($actionNodeRepository, 'deleteActionDependentData'))
                    return;
                $actionNodeRepository->deleteActionDependentData($actionNode, true);
            }
        }
        catch (\Exception $e) {
		    return;
        }
    }
}
