<?php

namespace Modules\Integration\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Integration\Contracts\WebhooksProcessingContract;
use Modules\Integration\Facades\IntegrationStorage;
use Modules\Integration\Jobs\ProcessWebhooks;
use App\Facades\JobHelper;
use Modules\YandexMetrica\Services\ProcessWebhooksService;

class WebhooksProcessingProvider extends ServiceProvider
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
        $this->app->bind(WebhooksProcessingContract::class, function($app, $parameters) {
            $integration = $parameters['integration'];
            $hook = $parameters['hook'];
            $processingEntityData = $parameters['processingEntityData'];

            $integrationData = IntegrationStorage::load($integration);
            $actionNodeApp = $integrationData->actionNodeApplication;
            switch ($actionNodeApp->type) {
                case 'yandex_metrica':
                    $service = app()->make(ProcessWebhooksService::class);
                    $service->setIntegration($integration);
                    $service->setWebhookData($processingEntityData);
                    $service->process();
                    break;
                default:
                    if(config('queue.use_queue')) {
                        ProcessWebhooks::dispatch($integration, $hook, $processingEntityData)->delay(JobHelper::getDelay());
                    } else {
                        ProcessWebhooks::dispatchNow($integration, $hook, $processingEntityData);
                    }
            }
        });
    }
}
