<?php

namespace Modules\Integration\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Integration\Repositories\IntegrationRepository;

class IntegrationRepositoryProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('integration_repository', function ($app) {
            return $app->make(IntegrationRepository::class);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'integration_repository'
        ];
    }

}
