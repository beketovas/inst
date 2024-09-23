<?php

namespace Modules\Application\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Application\Contracts\NodeRepositoryContract;
use Modules\Integration\Exceptions\NodeRepositoryException;

class NodeRepositoryProvider extends ServiceProvider
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
        // Create node builder
        $this->app->bind(NodeRepositoryContract::class, function($app, $parameters) {
            $node = $parameters['node'];
            $application = $node->application;
            if(!$application) {
                throw new NodeRepositoryException("Node needs to have selected application to get node repository using this application.");
            }

            $repositoryClass = 'Modules\\'.studly_case($application->type).'\\Repositories\\NodeRepository';

            $entityClass = 'Modules\\'.studly_case($application->type).'\\Entities\\Node';

            return $app->makeWith($repositoryClass, [new $entityClass]);

        });
    }
}
