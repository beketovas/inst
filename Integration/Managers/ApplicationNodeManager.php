<?php

namespace Modules\Integration\Managers;

use Apiway\IncomingData\Contracts\IncomingDataEntity;
use Modules\Integration\Contracts\ApplicationNode;
use Modules\Integration\Entities\Node;
use Modules\Integration\Contracts\ApplicationNodeManager as ApplicationNodeManagerContract;
use Modules\Integration\Storage\Integration;
use Modules\Synchronization\Services\Executor;
use Modules\Synchronization\Services\Sender;

class ApplicationNodeManager implements ApplicationNodeManagerContract
{
    private Node $node;

    public function __construct(ApplicationNode $node)
    {
        $this->node = $node->node;
    }

    public function createExecutor(Node $node)
    {
        if(class_exists('Modules\\'.studly_case($this->node->application_type.'\\Helpers\\Executor')))
            return app()->makeWith('Modules\\'.studly_case($this->node->application_type.'\\Helpers\\Executor'), ['node' => $node]);
        return app()->makeWith(Executor::class, ['node' => $node]);
    }

    public function createSender(Node $node, IncomingDataEntity $data) {
        if(class_exists('Modules\\'.studly_case($this->node->application_type.'\\Helpers\\Sender')))
            return app()->makeWith('Modules\\'.studly_case($this->node->application_type.'\\Helpers\\Sender'), ['node' => $node, 'data' => $data]);
        return app()->makeWith(Sender::class, ['node' => $node, 'data' => $data]);
    }
}
