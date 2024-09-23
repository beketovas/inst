<?php

namespace Modules\Integration\Events;

use Apiway\Hooks\Contracts\Hook;
use Apiway\IncomingData\Contracts\IncomingDataEntity;
use Modules\Integration\Contracts\SynchronizationProcessingEntityData;
use Modules\Integration\Entities\Node;
use Illuminate\Queue\SerializesModels;

class SynchronizationCompleted
{
    use SerializesModels;

    /**
     * @var Node
     */
    public Node $node;

    /**
     * @var Hook
     */
    public Hook $entity;

    /**
     * @var IncomingDataEntity
     */
    public IncomingDataEntity $data;

    /**
     * Create a new event instance.
     *
     * @param Node $node
     * @param Hook $hook
     * @param IncomingDataEntity $processingEntityData
     */
    public function __construct(Node $node,
                                Hook $hook,
                                IncomingDataEntity $processingEntityData)
    {
        $this->node = $node;
        $this->entity = $hook;
        $this->data = $processingEntityData;
    }
}
