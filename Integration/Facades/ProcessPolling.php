<?php

namespace Modules\Integration\Facades;

use Modules\Integration\Services\ProcessPolling as ProcessPollingService;
use Modules\Integration\Entities\NodePolling;

class ProcessPolling
{
    static function create(NodePolling $subscription): ProcessPollingService
    {
        return  app(ProcessPollingService::class, ['subscription' => $subscription]);
    }
}
