<?php

namespace Modules\Integration\Services;

use Modules\Integration\Contracts\IncomingDataPreProcessingHandler as IncomingDataPreProcessingHandlerContract;
use Modules\Integration\Contracts\WebhookHandler;
use Modules\Synchronization\Contracts\IncomingDataHandler as IncomingDataHandlerInterface;

class IncomingDataPreProcessingHandler implements IncomingDataHandlerInterface
{

    protected WebhookHandler $webhookHandler;

    public function __construct(WebhookHandler $webhookHandler)
    {
        $this->webhookHandler = $webhookHandler;
    }

    public function process() : void
    {
        app()->makeWith(IncomingDataPreProcessingHandlerContract::class, [
            'webhookHandler' => $this->webhookHandler,
        ]);
    }

}
