<?php

namespace Modules\Application\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Application\Contracts\NodeErrorsBuilderContract;
use Modules\Integration\Builders\Node\ApplicationBuilders\FactoryErrorBuilder;
use Modules\Integration\Exceptions\NodeErrorsBuilderException;

class NodeErrorsBuilderProvider extends ServiceProvider
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
        $this->app->bind(NodeErrorsBuilderContract::class, function($app, $parameters) {
            $node = $parameters['node'];
            $application = $node->application;
            if(!$application) {
                throw new NodeErrorsBuilderException("Node needs to have selected application to get node errors builder using this application.");
            }

            return FactoryErrorBuilder::getInstance($node);

        });
    }
}
