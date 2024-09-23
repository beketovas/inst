<?php

namespace Modules\Integration\Services;

use Apiway\IncomingData\Contracts\IncomingDataEntity;
use Modules\Integration\Contracts\WebhooksProcessingContract;
use Modules\Integration\Entities\Integration;
use Apiway\Hooks\Contracts\Hook;
use Modules\Synchronization\Contracts\IncomingDataHandler as IncomingDataHandlerInterface;

class IncomingDataHandler implements IncomingDataHandlerInterface
{

    protected Integration $integration;

    protected Hook $entity;

    protected IncomingDataEntity $data;

    public function __construct(Integration $integration, Hook $entity, IncomingDataEntity $data)
    {
        $this->integration = $integration;
        $this->entity = $entity;
        $this->data = $data;
    }

    public function process() : void
    {
        app()->makeWith(WebhooksProcessingContract::class, [
            'integration' => $this->integration,
            'hook' => $this->entity,
            'processingEntityData' => $this->data
        ]);
    }

}
