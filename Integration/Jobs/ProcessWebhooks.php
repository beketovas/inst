<?php

namespace Modules\Integration\Jobs;

use Apiway\IncomingData\Contracts\IncomingDataEntity;
use Apiway\SynchronizationLog\Events\SynchronizationEnded;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Modules\Integration\Entities\Integration;
use Modules\Integration\Events\SynchronizationCompleted;
use Modules\Integration\Facades\ApplicationNodeManagerFacade;
use Modules\Integration\Facades\IntegrationStorage;
use Apiway\Hooks\Contracts\Hook;
use Modules\Synchronization\Exceptions\ApiUnauthenticatedException;
use Modules\Synchronization\Exceptions\ConditionNotPassedException;
use Modules\Synchronization\Exceptions\DataPrepareException;
use Modules\Synchronization\Exceptions\ExternalServerErrorException;
use Modules\Synchronization\Exceptions\SynchronizationCompletedWithErrorException;
use Modules\Synchronization\Services\SynchronizationService;

use Illuminate\Support\Facades\Log;
use Exception;
use Modules\Application\Exceptions\ApplicationRateLimitException;
use Modules\Synchronization\Exceptions\SynchronizationException;
use Throwable;

class ProcessWebhooks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * @var Integration
     */
    protected $integration;

    /**
     * @var Hook
     */
    protected $entity;

    /**
     * @var IncomingDataEntity
     */
    protected $data;
    /**
     * Create a new job instance.
     *
     * @param Integration $integration
     * @param Hook $hook
     * @param IncomingDataEntity $processingEntityData
     */
    public function __construct(
        Integration $integration,
        Hook $hook,
        IncomingDataEntity $processingEntityData
    )
    {
        $this->integration = $integration;
        $this->entity = $hook;
        $this->data = $processingEntityData;
        $this->tries = config('queue.tries');
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::channel('synchronization')->info(__('site.log_section_separator'));

        $integrationStorage = IntegrationStorage::load($this->integration);

        $triggerAppNode = $integrationStorage->triggerAppNode;
        $actionAppNode = $integrationStorage->actionAppNode;

        $actionType = isset($triggerAppNode->action) ? $triggerAppNode->action->task : 'The application has no triggers';

        Log::channel('synchronization')->info("Receiving webhook. Processing entity ".$this->entity->getId().".");
        Log::channel('synchronization')->info("Application: ".$integrationStorage->triggerNodeApplication->type.". Integration: ".$this->integration->id.". Trigger: ". $actionType);

        // Create sender
        $triggerNodeManager = ApplicationNodeManagerFacade::load($triggerAppNode);
        $sender = $triggerNodeManager->createSender($integrationStorage->triggerNode, $this->data);

        // Create executor
        $actionNodeManager = ApplicationNodeManagerFacade::load($actionAppNode);
        $executor = $actionNodeManager->createExecutor($integrationStorage->actionNode);

        // Synchronize
        $synchronizationService = new SynchronizationService($sender, $executor);
        try {
            $performedResult = $synchronizationService->synchronize();
            event(new SynchronizationEnded($integrationStorage, $this->data, 'success', json_encode($performedResult)));
            $this->completed();

            $aUserIdsLog = env('STRIPE_USER_IDS_DATA_TRANSFER_LOG', false);
            if($aUserIdsLog && $aUserIdsLog != '' && in_array($this->integration->user->id, explode(",", $aUserIdsLog))) {
                Log::channel('user_data_transfers_check')->info("Receiving webhook. Processing entity ".$this->entity->getId().".");
                Log::channel('user_data_transfers_check')->info("Application: ".$integrationStorage->triggerNodeApplication->type.". Integration: ".$this->integration->id.". Trigger: ". $actionType);
                Log::channel('user_data_transfers_check')->info(print_r($performedResult, true));
            }

            $this->integration->user->changeDataTransfers();
        }
        catch (ExternalServerErrorException | ApplicationRateLimitException | ConnectException $e) {
            Log::channel('synchronization')->info($e->getMessage());
            event(new SynchronizationEnded($integrationStorage, $this->data, 'error', $e->getMessage()));
            $this->release(config('queue.job_delay_api_rate_limit'));
        }
        catch (SynchronizationException | DataPrepareException | SynchronizationCompletedWithErrorException $e) {
            if(!($e instanceof SynchronizationCompletedWithErrorException))
                Log::channel('synchronization')->info($e->getMessage());
            event(new SynchronizationEnded($integrationStorage, $this->data, 'error',$e->getMessage()));
            $this->completed();
        }
        catch (ApiUnauthenticatedException $e) {
            event(new SynchronizationEnded($integrationStorage, $this->data, 'unauthenticated', $e->getMessage()));
            $this->completed();
        }
        catch (ConditionNotPassedException $e) {
            Log::channel('synchronization')->info($e->getMessage());
            event(new SynchronizationEnded($integrationStorage, $this->data, 'not_filtered', $e->getMessage()));
            $this->completed();
        }
        catch(Exception $e) {
            Log::channel('synchronization')->info("Synchronization was interrupted because of error");
            Log::channel('synchronization')->info($e);
            event(new SynchronizationEnded($integrationStorage, $this->data, 'error', $e->getMessage()));
            $this->completed();
        }
        Log::channel('synchronization')->info(__('site.log_section_separator'));
    }

    public function failed(Throwable $throwable)
    {
        Log::channel('queues')->warning('Queue: ProcessWebhooks. Error: '. $throwable->getMessage());
    }

    protected function completed()
    {
        $integrationStorage = IntegrationStorage::load($this->integration);
        $appType = $integrationStorage->triggerNodeApplication->type;
        $configAlias = preg_replace('/[^\p{L}\p{N}\s]/u', '', $appType);
        if(config($configAlias.'.separate_incoming_data')) {
            $eventClass = 'Modules\\'.studly_case($appType).'\\Events\\SynchronizationCompleted';
            event(new $eventClass($integrationStorage->triggerNode, $this->entity, $this->data));
        }
        else {
            event(new SynchronizationCompleted($integrationStorage->triggerNode, $this->entity, $this->data));
        }
    }

}
