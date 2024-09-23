<?php

namespace Modules\Integration\Services;

use Modules\Integration\Contracts\SynchronizationServiceContract;
use Modules\Integration\Contracts\SynchronizationProcessingEntityData;
use Modules\Integration\Entities\Integration;
use Modules\Integration\Facades\IntegrationStorage;
use Illuminate\Support\Facades\Log;

class SynchronizationService
{

    /**
     * @var Integration
     */
    protected $integration;

    /**
     * @var SynchronizationProcessingEntityData
     */
    protected $processingEntityData;

    /**
     * SynchronizationService constructor.
     * @param Integration $integration
     * @param SynchronizationProcessingEntityData $processingEntityData
     */
    public function __construct(
        Integration $integration,
        SynchronizationProcessingEntityData $processingEntityData)
    {
        $this->integration = $integration;
        $this->processingEntityData = $processingEntityData;
    }


    /**
     * Synchronize integration
     *
     * @return bool
     */
    public function synchronize()
    {
        $syncData = IntegrationStorage::load($this->integration);

        // Load trigger sync service
        $triggerSyncService = app()->makeWith(SynchronizationServiceContract::class, [
            'integration' => $this->integration,
            'application' => $syncData->triggerNodeApplication
        ]);
        $triggerSyncService->setProcessingEntityData($this->processingEntityData);
        $preparedData = $triggerSyncService->prepareData();

        if(!$preparedData) {
            Log::channel('synchronization')->info("No prepared webhook data.");
            return false;
        }
        Log::channel('synchronization')->info("Prepared data for ".$syncData->actionNodeApplication->type.": ".json_encode($preparedData, JSON_UNESCAPED_UNICODE));

        // Load action sync service
        $actionSyncService = app()->makeWith(SynchronizationServiceContract::class, [
            'integration' => $this->integration,
            'application' => $syncData->actionNodeApplication
        ]);
        $actionSyncService->setProcessingEntityData($this->processingEntityData);
        $actionSyncService->setPreparedData($preparedData);
        $sync = $actionSyncService->synchronize();
        if(!$sync) {
            return false;
        }

        // Make webhook data processed
        $triggerSyncService->makeProcessed($this->processingEntityData);

        return true;
    }

}
