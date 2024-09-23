<?php

namespace Modules\Instagram\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Instagram\Contracts\WebhookHandlerContract;
use Modules\Instagram\Subscription\Handlers\FbFeedsWebhookHandler;

class InstagramServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('instagram.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'instagram'
        );

        $serviceSecretPath = __DIR__.'/../Config/service_secret.php';
        if (file_exists($serviceSecretPath)) {
            $this->mergeConfigFrom($serviceSecretPath, 'instagram.service');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
