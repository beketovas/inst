<?php

namespace Modules\Integration\Repositories;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class PollingDataRedisRepository
{
    protected Connection $connection;

    public function __construct()
    {
        $this->connection = Redis::connection('polling_data');
    }

    public function deleteByNodePollingId(int $nodeId): bool
    {
        $keys = $this->connection->zRangeByScore('node_polling_id', $nodeId, $nodeId);
        foreach ($keys as $key) {
            $this->connection->zRem('application_id', $key);
            $this->connection->zRem('node_polling_id', $key);
            $this->connection->zRem('created_id', $key);
        }
        if(!empty($keys))
            $this->connection->del($keys);
        return true;
    }
}
