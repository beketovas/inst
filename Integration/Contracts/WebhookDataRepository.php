<?php

namespace Modules\Integration\Contracts;

interface WebhookDataRepository
{
    public function makeProcessed(SynchronizationProcessingEntityData $webhook);
}
