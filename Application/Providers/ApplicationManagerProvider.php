<?php

namespace Modules\Application\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Application\Facades\ApplicationManagerFacade;
use Modules\Application\Managers\ApplicationManager;

class ApplicationManagerProvider extends ServiceProvider
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
        $this->app->bind('application_manager', ApplicationManager::class);
        $this->app->alias('application_manager', ApplicationManagerFacade::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'application_manager'
        ];
    }
}
