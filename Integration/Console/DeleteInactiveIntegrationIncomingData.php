<?php

namespace Modules\Integration\Console;

use Illuminate\Console\Command;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use Modules\Integration\Entities\Integration;
use Modules\Integration\Facades\IntegrationRepository;

class DeleteInactiveIntegrationIncomingData extends Command
{
    protected $signature = 'redis:incoming-data-delete-inactivity';

    protected Connection $connection;

    public function __construct()
    {
        $this->connection = Redis::connection('incoming_data');
        parent::__construct();
    }

    public function handle()
    {
        $integrations = IntegrationRepository::getInactive();
        $keys = collect();
        $integrations->each(function(Integration $integration) use(&$keys) {
            $keys->push($this->connection->zRangeByScore('integration_id', $integration->id, $integration->id));
        });
        foreach ($keys->collapse() as $key) {
            $this->connection->zRem('application_id', $key);
            $this->connection->zRem('subscription_id', $key);
            $this->connection->zRem('integration_id', $key);
            $this->connection->zRem('created_id', $key);
        }
        if(!empty($keys))
            $this->connection->del($keys);
        $this->info("Related incoming_data with inactive integrations were deleted ({$keys->collapse()->count()} records).");
    }
}
