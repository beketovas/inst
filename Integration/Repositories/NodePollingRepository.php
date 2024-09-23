<?php declare(strict_types=1);

namespace Modules\Integration\Repositories;

use App\Traits\CacheBuilder;
use Modules\Integration\Entities\NodePolling;

class NodePollingRepository
{
    use CacheBuilder;

    protected NodePolling $model;

    public function __construct(NodePolling $nodePolling)
    {
        $this->model = $nodePolling;
    }

    public function getByApplicationType(string $appType)
    {
        return $this->model->where('application_type', $appType);
    }

    public function create(array $attributes)
    {
        $this->cacheForget(
            ['node_polling', 'all'],
            ['node_polling', 'application_'.$attributes['application_type']],
            ['node_polling', 'application_'.$attributes['application_type'].'_trigger_'.$attributes['trigger_type']],
        );
        return NodePolling::create($attributes);
    }

    public function getByFilter(array $filter)
    {
        $cacheKey= 'all';
        if(isset($filter['application']))
            $cacheKey = 'application_'.$filter['application'];
        else if(isset($filter['application']) && isset($filter['trigger']))
            $cacheKey = 'application_'.$filter['application'].'_trigger_'.$filter['trigger'];

        return $this->cacheRemember(['node_polling', $cacheKey], function() use ($filter) {
            return $this->model
                ->when(isset($filter['application']), function ($query) use ($filter) {
                    $query->where('application_type', $filter['application']);
                })
                ->when(isset($filter['trigger']), function ($query) use ($filter) {
                    $query->where('trigger_type', $filter['trigger']);
                })
                ->with('node')
                ->get();
        });
    }

    public function getByNodeId(int $nodeId)
    {
        return $this->model->where('node_id', $nodeId)->get();
    }

    public function deleteByNodeId(int $nodeId): bool
    {
        $records = $this->model->where('node_id', $nodeId)->get();
        foreach ($records as $record) {
            if (!isset($record))
                continue;
            $record->flushCache();
            try {
                $record->delete();
            } catch (\Error $e) {
                continue;
            }
        }
        return true;
    }
}
