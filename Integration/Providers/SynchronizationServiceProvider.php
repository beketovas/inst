<?php

namespace Modules\Integration\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Integration\Contracts\SynchronizationServiceContract;
use Modules\Synchronization\Exceptions\SynchronizationException;

class SynchronizationServiceProvider extends ServiceProvider
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
        // Create an integration api service
        $this->app->bind(SynchronizationServiceContract::class, function($app, $parameters) {
            if(empty($parameters['application'])) {
                throw new SynchronizationException("Application is empty.");
            }
            $application = $parameters['application'];
            $class = "Modules\\".studly_case($application->type)."\\Services\\SynchronizationService";

            $integrationService = $app->makeWith($class, ['integration' => $parameters['integration']]);

            return $integrationService;

        });
    }
}
