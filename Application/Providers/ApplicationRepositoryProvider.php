<?php

namespace Modules\Application\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Application\Repositories\ApplicationRepository;

class ApplicationRepositoryProvider extends ServiceProvider
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
        $this->app->singleton('application_repository', function ($app) {
            return $app->make(ApplicationRepository::class);
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
            'application_repository'
        ];
    }

}
