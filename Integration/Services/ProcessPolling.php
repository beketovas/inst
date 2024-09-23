<?php

namespace Modules\Integration\Services;

use Apiway\ApiRequest\Client;
use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use Apiway\IncomingData\Contracts\IncomingDataRepository;
use Apiway\NodePollingData\Contracts\NodePollingDataRepository;
use App\Traits\CacheBuilder;
use Illuminate\Support\Facades\Log;
use Modules\Integration\Entities\Node;
use Modules\Integration\Entities\NodePolling;
use Modules\Application\Contracts\AccountModelContract;

class ProcessPolling
{
    use ConfigHelper, CacheBuilder;

    protected NodePolling $nodePolling;

    protected IncomingDataRepository $incomingDataRepository;

    protected NodePollingDataRepository $nodePollingDataRepository;

    protected Node $node;

    protected $subscription;

    public function __construct(NodePolling $subscription, IncomingDataRepository $incomingDataRepository, NodePollingDataRepository $nodePollingDataRepository)
    {
        $this->incomingDataRepository = $incomingDataRepository;
        $this->nodePolling = $subscription;
        $this->nodePollingDataRepository = $nodePollingDataRepository;
    }

    /**
     * @throws \Apiway\ApiRequest\Exceptions\UndefinedMethod
     * @throws \Modules\Integration\Exceptions\NodeException
     */
    public function process(bool $sync = false): bool
    {
        $this->node = $this->nodePolling->node;
        $nodeMapper = $this->node->nodeMapper($this->nodePolling->application_type);
        $this->subscription = $nodeMapper->subscription();

        if(!$this->subscription) {
            Log::channel('polling_data')->warning('Polling: '. $this->nodePolling->id . '. Application subscription not found, maybe because integration was deactivated');
            return false;
        }

        $config = $this->getApiConfig($this->nodePolling->application_type, 'triggers_polling');
        $triggerType = $this->nodePolling->trigger_type;
        $methodConfig = $config[$triggerType];

        $fieldRepository = app('Modules\\'.studly_case($this->nodePolling->application_type).'\\Repositories\\FieldRepository');
        $settings = $this->cacheRemember(['node_polling', 'settings_id_'.$this->subscription->id], function() use ($nodeMapper, $fieldRepository) {
            return $fieldRepository->getWithValuesOnly(['appNodeId' => $nodeMapper->applicationNode()->id]);
        });

        $modifiedSettings = $settings->mapWithKeys(function($item) {
            return [$item->identifier => $item];
        });
        $preparedParams = array_merge($this->subscription->toArray(), $modifiedSettings->all());
        $account = $nodeMapper->account();

        if ($account instanceof AccountModelContract) {
            $client = new Client($config, $account);
        } else {
            Log::channel('polling_data')->warning('Polling: '. $this->nodePolling->id . '. User without account for this polling');
            return false;
        }

        $res = $client->call($triggerType, $preparedParams);
        if($res->getError()) {
            $this->log($triggerType, $res->getError(), -1);
            return false;
        }

        $lastFullPollingData = $this->nodePollingDataRepository->lastItem($this->nodePolling->id);
        $fullPollingData = [
            "app_type" => $this->nodePolling->application_type,
            "node_polling_id" => $this->nodePolling->id,
            "polling_data" => json_encode($res->getResponse()),
            "application_id" => $this->node->application_id,
        ];

        if($sync) {
            try {
                $this->nodePollingDataRepository->saveNew($fullPollingData);
                $this->log($triggerType, $fullPollingData['polling_data'], 0);
            } catch (\Throwable $e) {
                $this->log($triggerType, $e->getMessage(), -1);
            }
            return true;
        }

        $apiEndpointsConfig = $this->getApiConfig($this->nodePolling->application_type, 'api_endpoints');
        $filteredData = $methodConfig['check'](
            $res->getResponse(),
            $lastFullPollingData,
            $settings,
            new Client(
                $apiEndpointsConfig,
                $account
            )
        );

        if(!empty($filteredData) || is_null($filteredData)) {
            try {
                $this->nodePollingDataRepository->saveNew($fullPollingData);
                $this->log($triggerType, $fullPollingData['polling_data'], 1);
            } catch (\Throwable $e) {
                $this->log($triggerType, $e->getMessage(), -1);
            }
        }

        if(is_null(($filteredData)))
            return true;

        foreach ($filteredData as $result) {
            $this->pushToQueue($result);
        }
        return true;
    }

    /**
     * @throws \Modules\Integration\Exceptions\NodeException
     */
    protected function pushToQueue(object $filteredObject)
    {
        $integration = $this->node->nodeMapper($this->nodePolling->application_type)->integration();
        $data = [
            "app_type" => $this->nodePolling->application_type,
            "integration_id" => $integration->id,
            "webhook_data" => json_encode($filteredObject),
            "application_id" => $this->node->application_id,
            "processed" => 0
        ];
        $webhookData = $this->incomingDataRepository->saveNew($data);
        $incomingDataHandler = new IncomingDataHandler($integration, $this->subscription, $webhookData);
        $incomingDataHandler->process();
    }

    protected function log(string $triggerType, string $result, int $type)
    {
        if(config('app.debug_polling') != true)
            return;
        Log::channel('polling_data')->info(__('site.log_section_separator'));
        $message = '';
        switch ($type) {
            case -1:
                $message = 'Polling is failed';
                break;
            case 0:
                $message = 'Sync data successfully added to node_polling_data table';
                break;
            case 1:
                $message = 'New data successfully added to node_polling_data table';
                break;
        }
        Log::channel('polling_data')->info($message.'. Type: ' . $triggerType . '. Application: '.$this->nodePolling->application_type.'. Node id: ' . $this->nodePolling->node_id);
        Log::channel('polling_data')->info($result);
        Log::channel('polling_data')->info(__('site.log_section_separator'));
    }
}
