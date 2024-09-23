<?php

namespace Modules\Integration\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Integration\Contracts\IntegrationServiceContract;
use Modules\Integration\Exceptions\IntegrationException;

class IntegrationProvider extends ServiceProvider
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
        $this->app->bind(IntegrationServiceContract::class, function($app, $parameters) {
            if(empty($parameters['integration'])) {
                throw new IntegrationException("Integration entity necessary to create integration service.");
            }

            $integrationService = $app->makeWith('Modules\Integration\Services\IntegrationService', ['integration' => $parameters['integration']]);

            return $integrationService;

        });
    }
}
