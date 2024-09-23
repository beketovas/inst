<?php declare(strict_types=1);

namespace Modules\Integration\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Integration\Contracts\ApplicationNode;
use Modules\Integration\Managers\ApplicationNodeManager;

class ApplicationNodeManagerFacade extends Facade
{

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'applicaiton_node_manager.singleton';
    }

    /**
     * Load
     *
     * @param ApplicationNode $node
     * @return mixed|ApplicationNodeManagerFacade|object
     */
    public static function load(ApplicationNode $node) {
        return app()->makeWith(ApplicationNodeManager::class, ['node' => $node]);
    }
}
