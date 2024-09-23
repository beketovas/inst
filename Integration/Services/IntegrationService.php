<?php

namespace Modules\Integration\Services;

use Apiway\IncomingData\Contracts\IncomingDataRepository;
use Apiway\IntegrationLog\Events\IntegrationChanged;
use App\Helpers\FileHelper;
use Exception;
use Modules\Integration\Entities\Integration;
use Modules\Application\Contracts\NodeErrorsBuilderContract;
use Modules\Application\Contracts\IntegrationServiceContract as AppIntegrationServiceContract;
use Modules\Integration\Events\IntegrationActivated;
use Modules\Integration\Facades\IntegrationStorage;
use Modules\Integration\Repositories\IntegrationRepository;
use Illuminate\Support\Facades\Log;
use Modules\Integration\Repositories\NodePollingRepository;
use Modules\Integration\Repositories\PollingDataRedisRepository;
use Modules\YandexMetrica\Repositories\CounterRepository;

class IntegrationService implements AppIntegrationServiceContract
{
    protected Integration $integration;

    protected string $performedBy;
    protected string $command;
    protected string $reason;

    protected IntegrationRepository $integrationRepository;

    protected NodePollingRepository $nodePollingRepository;

    protected IncomingDataRepository $incomingDataRepository;

    protected PollingDataRedisRepository $pollingDataRedisRepository;

    /**
     * IntegrationService constructor.
     * @param Integration $integration
     * @param IntegrationRepository $integrationRepository
     */
    public function __construct(
        Integration $integration,
        IntegrationRepository $integrationRepository,
        NodePollingRepository $nodePollingRepository,
        IncomingDataRepository $incomingDataRedis,
        PollingDataRedisRepository $pollingDataRedisRepository
    )
    {
        $this->integration = $integration;
        $this->integrationRepository = $integrationRepository;
        $this->nodePollingRepository = $nodePollingRepository;
        $this->incomingDataRepository = $incomingDataRedis;
        $this->pollingDataRedisRepository = $pollingDataRedisRepository;

        $this->performedBy = 'user';
        $this->reason = '';
        $this->command = '';
    }

    /**
     * Check if integration has errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        $triggerNode = $this->integration->triggerNode();
        if(!$triggerNode->application_id) return true;

        $triggerErrorsBuilder = app()->makeWith(NodeErrorsBuilderContract::class, ['node' => $triggerNode]);
        $triggerErrorsBuilder->build();
        $triggerNodeErrors = $triggerErrorsBuilder->getErrors();

        $actionNode = $this->integration->actionNode();
        if(!$actionNode->application_id) return true;

        $actionErrorsBuilder = app()->makeWith(NodeErrorsBuilderContract::class, ['node' => $actionNode]);
        $actionErrorsBuilder->build();
        $actionNodeErrors = $actionErrorsBuilder->getErrors();

        $hasErrors = (!empty($triggerNodeErrors) || !empty($actionNodeErrors)) ?  true : false;

        return $hasErrors;
    }

    /**
     * Activate integration
     *
     * @return bool
     */
    public function activate()
    {
        $triggerNode = $this->integration->triggerNode();

        $applicationIntegrationService = app()->makeWith(AppIntegrationServiceContract::class, [
            'node' => $triggerNode
        ]);
        $activated = $applicationIntegrationService->activate();
        if($activated) {
            $this->integrationRepository->changeActive($this->integration, true);

            $integrationStorage = IntegrationStorage::load($this->integration);
            event(new IntegrationChanged($integrationStorage, 'activated', $this->performedBy, $this->reason, $this->command));

            event(new IntegrationActivated($triggerNode));
            return true;
        }

        return false;
    }

    public function activateBy(string $performedBy = 'user', string $command = '', string $reason = '')
    {
        $this->performedBy = $performedBy;
        $this->command = $command;
        $this->reason = $reason;
    }

    /**
     * Deactivate integration
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        $nodes = $this->integration->nodes()->get();
        $trigger = $nodes[0];

        try {
            $applicationIntegrationService = app()->makeWith(AppIntegrationServiceContract::class, [
                'node' => $trigger
            ]);
            $deactivated = $applicationIntegrationService->deactivate();
        }
        catch (\Exception $e) {
            Log::channel('integrations')->warning('Integration '.$this->integration->id.', user id '.$this->integration->user_id.' was deactivated with errors. Response: '.$e->getMessage());
            $this->deactivateWithErrors($trigger);
            return true;
        }


        if($nodes[1]->application_type == 'yandex_metrica') {
            $yandexMetricaCounterRepository = app(CounterRepository::class);
            $yandexMetricaCounterRepository->deleteCounterByNodeId($nodes[1]->id);
        }

        $integrationStorage = IntegrationStorage::load($this->integration);
        event(new IntegrationChanged($integrationStorage, 'deactivated', $this->performedBy, $this->reason, $this->command));

        // Delete ways uploads dir
        try {
            FileHelper::removeDirectory(config('app.app_storage_path.ways') . '/' . $this->integration->id);
        } catch (Exception $e) {
            Log::warning($e);
        }

        $nodePollings = $this->nodePollingRepository->getByNodeId($trigger->id);
        foreach ($nodePollings as $nodePolling) {
            $this->pollingDataRedisRepository->deleteByNodePollingId($nodePolling->id);
        }

        $this->integrationRepository->changeActive($this->integration, false);
        $this->integrationRepository->removeWarning($this->integration);
        $this->nodePollingRepository->deleteByNodeId($trigger->id);

        if($deactivated)
            return true;

        return false;
    }

    /**
     * Deactivate integration if has errors
     *
     * @return bool
     */
    public function deactivateWithErrors($trigger): bool
    {
        $this->integrationRepository->changeActive($this->integration, false);
        $this->integrationRepository->removeWarning($this->integration);
        if(isset($trigger->id)) {
            $nodePollings = $this->nodePollingRepository->getByNodeId($trigger->id);
            foreach ($nodePollings as $nodePolling) {
                $this->pollingDataRedisRepository->deleteByNodePollingId($nodePolling->id);
            }
            $this->nodePollingRepository->deleteByNodeId($trigger->id);
        }
        if($this->integration)
            $this->integration->flushCache();
        return true;
    }

    public function deactivateBy(string $performedBy = 'user', string $command = '', string $reason = ''): bool
    {
        $this->performedBy = $performedBy;
        $this->command = $command;
        $this->reason = $reason;
        return $this->deactivate();
    }
}
