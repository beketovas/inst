<?php

namespace Modules\Application\Providers;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Modules\Application\Contracts\ActionRepositoryContract;

class ActionRepositoryProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ActionRepositoryContract::class, function(Container $app, array $parameters) {
            $applicationType = $parameters['app_type'];

            $serviceName = "Modules\\".studly_case($applicationType)."\\Repositories\\ActionRepository";
            if(!class_exists($serviceName))
                return null;

            return $app->make($serviceName);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ActionRepositoryContract::class];
    }
}
