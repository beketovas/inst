<?php

namespace Modules\Application\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Application\Services\ApplicationAccount;

class ApplicationAccountProvider extends ServiceProvider
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
        $this->app->singleton('application_account', function ($app) {
            return $app->makeWith(ApplicationAccount::class);
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
            'application_account'
        ];
    }

}
