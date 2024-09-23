<?php

namespace Modules\Application\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Application\Contracts\IntegrationServiceContract;

class IntegrationServiceProvider extends ServiceProvider
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
        // Create an application api service
        $this->app->bind(IntegrationServiceContract::class, function($app, $parameters) {
            $application = $parameters['node']->application;

            $serviceName = "Modules\\".studly_case($application->type)."\\Services\\IntegrationService";

            $applicationService = $app->makeWith($serviceName, ['node' => $parameters['node']]);

            return $applicationService;

        });
    }
}
