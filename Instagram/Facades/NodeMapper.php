<?php

namespace Modules\Instagram\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Integration\Entities\Node as NodeEntity;
use Modules\Instagram\Mappers\Node;

class NodeMapper extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'instagram_node_storage';
    }

    /**
     * Load storage
     *
     * @param NodeEntity $node
     * @return Node
     */
    public static function load(NodeEntity $node) {
        $storage = app()->makeWith(Node::class, ['node' => $node]);

        return $storage;
    }
}
