<?php

namespace Modules\Integration\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Integration\Contracts\WebhookHandler;

class PreProcessWebhooks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    protected WebhookHandler $webhookHandler;

    public function __construct(WebhookHandler $webhookHandler)
    {
        $this->webhookHandler = $webhookHandler;
        $this->tries = config('queue.tries');
    }

    public function handle()
    {
        try {
            $this->webhookHandler->process();
        } catch (\Exception $e) {
            Log::channel('webhooks')->warning('Webhook processing error. '.$e);
        }
    }

}
