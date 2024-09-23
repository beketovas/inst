<?php declare(strict_types=1);

namespace Modules\Integration\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Integration\Contracts\BaseNode;
use Modules\Integration\Managers\NodeManager;

class NodeManagerFacade extends Facade
{

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'node_manager.singleton';
    }

    /**
     * Load
     *
     * @param BaseNode $node
     * @return NodeManager
     */
    public static function load(BaseNode $node) {
        return app()->makeWith(NodeManager::class, ['node' => $node]);
    }
}
