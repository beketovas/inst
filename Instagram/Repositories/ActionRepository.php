<?php

namespace Modules\Instagram\Repositories;

use App\Traits\CacheBuilder;
use Illuminate\Support\Collection;
use Modules\Application\Contracts\ActionRepositoryContract;
use Modules\Instagram\Entities\Action;
use Modules\Instagram\Entities\Node;

class ActionRepository implements ActionRepositoryContract
{

    use CacheBuilder;

    /**
     * @var Action
     */
    protected $model;

    /**
     * Repository constructor.
     *
     * @param Action $action
     */
    public function __construct(Action $action)
    {
        $this->model = $action;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return Action
     */
    public function getById($id)
    {
        $action = $this->model->find($id);
        return $action;
    }

    /**
     * @return Collection
     */
    public function getForTrigger()
    {
        return $this->model->where('for_trigger', true)->get();
    }

    /**
     * @return Collection
     */
    public function getForAction()
    {
        return $this->model->where('for_action', true)->get();
    }

    /**
     * @param array $filter
     * @return Collection
     */
    public function getWithIntegration(array $filter)
    {
        $query = $this->model->select(Action::TABLE_NAME.'.*', 'integrations.name as integration_name', 'integrations.active', 'integrations.id as integration_id')
            ->leftJoin('instagram_nodes', Action::TABLE_NAME.'.id', Node::TABLE_NAME.'.action_id')
            ->leftJoin('integration_nodes', Node::TABLE_NAME.'.node_id', 'integration_nodes.id')
            ->leftJoin('integrations', 'integrations.id', 'integration_nodes.integration_id')
            ->whereNotNull(Node::TABLE_NAME.'.action_id');
        if(isset($filter['tasks']))
            $query->whereIn('task', $filter['tasks']);
        if(isset($filter['accountId'])) {
            $query->where('integration_nodes.account_id', $filter['accountId'])
                    ->whereNotNull('integration_nodes.account_id');
        }
        if(isset($filter['userId']))
            $query->where('integrations.user_id', $filter['userId']);

        return $query->get();
    }

    public function actionsCount(): int
    {
        return $this->cacheForever(['application_'.config('instagram.type'), 'actions_count'], function() {
            return $this->model->where('for_action', true)->count();
        });
    }

    public function triggersCount(): int
    {
        return $this->cacheForever(['application_'.config('instagram.type'), 'triggers_count'], function() {
            return $this->model->where('for_trigger', true)->count();
        });
    }
}
