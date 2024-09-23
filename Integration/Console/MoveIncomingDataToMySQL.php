<?php

namespace Modules\Integration\Console;

use Apiway\IncomingData\Entities\MySQL\IncomingData;
use Apiway\IncomingData\Repositories\Redis\IncomingDataRepository;
use Illuminate\Console\Command;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;
use Modules\Integration\Facades\IntegrationRepository;

class MoveIncomingDataToMySQL extends Command
{
    protected $signature = 'redis:move-incoming-data-to-mysql';

    protected Connection $connection;

    protected IncomingDataRepository $incomingDataRedisRepository;

    public function __construct(IncomingDataRepository $incomingDataRedisRepository)
    {
        $this->connection = Redis::connection('incoming_data');
        $this->incomingDataRedisRepository = $incomingDataRedisRepository;
        parent::__construct();
    }

    public function handle()
    {
        $keys = $this->connection->keys('*.*');
        $incomingData = [];
        $numberQuery = 0;
        $integrationIds = [];
        $i = 0;
        foreach ($keys as $key) {
            if($i == 100) {
                ++$numberQuery;
                $i = 0;
            }

            $item = $this->connection->hgetall($key);
            $createdAt = $this->connection->zscore('created_at', $key);
            $appId = $this->connection->zscore('application_id', $key);

            $integration = IntegrationRepository::getById($item['integration_id']);
            if(!$integration || $integration->active == false || !$createdAt) {
                //$this->incomingDataRedisRepository->deleteByIntegrationId($item['integration_id']);
                continue;
            }

            $incomingData[$numberQuery][] = [
                'integration_id' => $item['integration_id'],
                'application_type' => $item['app_type'],
                'webhook_data' => $item['webhook_data'],
                'subscription_id' => $item['subscription_id'] ?? null,
                'application_id' => $appId,
                'processed' => isset($item['processed']) && $item['processed'],
                'created_at' => (new Carbon())->setTimestamp($createdAt)
            ];
            $integrationIds[] = $item['integration_id'];
            ++$i;
        }

        foreach ($incomingData as $data) {
            IncomingData::insert($data);
        }

        $this->info('Incoming data successfully moved to MySQL');
    }
}
