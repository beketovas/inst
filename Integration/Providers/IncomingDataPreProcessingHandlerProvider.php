<?php

namespace Modules\Integration\Providers;

use Illuminate\Support\ServiceProvider;

use App\Facades\JobHelper;
use Modules\Integration\Contracts\IncomingDataPreProcessingHandler;
use Modules\Integration\Jobs\PreProcessWebhooks;

class IncomingDataPreProcessingHandlerProvider extends ServiceProvider
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
        $this->app->bind(IncomingDataPreProcessingHandler::class, function($app, $parameters) {
            $webhookHandler = $parameters['webhookHandler'];
            if(config('queue.use_queue')) {
                PreProcessWebhooks::dispatch($webhookHandler)->delay(JobHelper::getDelay());
            } else {
                PreProcessWebhooks::dispatchNow($webhookHandler);
            }
        });
    }
}
