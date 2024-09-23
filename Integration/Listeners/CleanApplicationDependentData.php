<?php

namespace Modules\Integration\Listeners;

use Modules\Integration\Events\AfterApplicationChange;
use Modules\Integration\Events\BeforeApplicationChange;
use Modules\Integration\Repositories\NodeRepository;

class CleanApplicationDependentData
{

    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * Create the event listener.
     *
     * @param NodeRepository $nodeRepository
     */
    public function __construct(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * Handle the event.
     *
     * @param BeforeApplicationChange $event
     * @return void
     */
    public function handle(BeforeApplicationChange $event)
    {
        $node = $event->node;
        $this->nodeRepository->deleteApplicationDependentData($node);

        // If node is trigger, delete all data from the action node
        if($node->isTrigger()) {
            $actionNode = $node->nextNode();
            $this->nodeRepository->deleteApplicationDependentData($actionNode, true);
        }
    }
}
