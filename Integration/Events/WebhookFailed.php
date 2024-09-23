<?php

namespace Modules\Integration\Events;

use Modules\Integration\Entities\Integration;

class WebhookFailed
{
    public Integration $integration;

    public string $reason;

    public string $appType;

    public string $method;

    public int $accountId;

    public function __construct(Integration $integration, string $reason, string $appType, string $method, int $accountId)
    {
        $this->integration = $integration;
        $this->reason = $reason;
        $this->appType = $appType;
        $this->method = $method;
        $this->accountId = $accountId;
    }
}
