<?php

namespace Modules\Integration\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Integration\Contracts\ApplicationNodeManager as ApplicationNodeManagerContract;
use Modules\Integration\Managers\ApplicationNodeManager;
use Modules\Synchronization\Exceptions\SynchronizationException;

class ApplicationNodeManagerProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ApplicationNodeManagerContract::class, function($app, $parameters) {
            if(empty($parameters['node'])) {
                throw new SynchronizationException("Node is empty.");
            }
            $node = $parameters['node'];

             return $app->makeWith(ApplicationNodeManager::class, ['node' => $node]);
        });
    }
}
