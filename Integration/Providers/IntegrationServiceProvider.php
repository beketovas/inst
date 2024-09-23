<?php

namespace Modules\Integration\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Integration\Console\ActivateAccidentallyDeactivatedIntegrations;
use Modules\Integration\Console\ActivateIntegration;
use Modules\Integration\Console\DeactivateIntegrations;
use Modules\Integration\Console\DeactivateNotWorkingIntegration;
use Modules\Integration\Console\DeactivateNotWorkingIntegrations;
use Modules\Integration\Console\DeleteInactiveIntegrationIncomingData;
use Modules\Integration\Console\IntegrationNotWorkingWarning;
use Modules\Integration\Console\MoveIncomingDataToMySQL;
use Modules\Integration\Console\NodeApplicationTypeMigration;

class IntegrationServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerCommands();
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
        $this->app->register(AuthServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('integration.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'integration'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'integration');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/integration');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'integration');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'integration');
        }
    }

    /**
    * Register additional commands
    *
    * @return void
    */
    public function registerCommands()
    {
        $this->commands([
            NodeApplicationTypeMigration::class,
            DeactivateIntegrations::class,
            DeleteInactiveIntegrationIncomingData::class,
            MoveIncomingDataToMySQL::class,
            DeactivateNotWorkingIntegrations::class,
            DeactivateNotWorkingIntegration::class,
            IntegrationNotWorkingWarning::class,
            ActivateAccidentallyDeactivatedIntegrations::class,
            ActivateIntegration::class
        ]);
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
