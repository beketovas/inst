<?php

namespace Modules\Integration\Repositories;

use App\Traits\CacheBuilder;
use Modules\Application\Entities\Application;
use Modules\Integration\Entities\Node;
use Modules\Integration\Entities\Integration;
use Modules\Application\Contracts\NodeRepositoryContract;

class NodeRepository
{
    use CacheBuilder;

    /**
     * @var Node
     */
    protected $model;

    /**
     * NodeRepository constructor.
     *
     * @param Node $node
     */
    public function __construct(Node $node)
    {
        $this->model = $node;
    }

    /**
     * Get nodes collection
     *
     * @param array $filter
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(array $filter = [])
    {
        $query = $this->model->select('*');
            if(isset($filter['applicationTypeIsNUll']))
                $query->whereNull('application_type');

        return $query->get();
    }

    /**
     * Get node by id
     *
     * @param int $id
     * @return Node
     */
    public function getById($id)
    {
        return $this->model->find($id);
    }

    /**
     * @param int $applicationId
     * @param int $accountId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByAppIdAndAccountId($applicationId, $accountId)
    {
        $result = $this->model
            ->where('application_id', $applicationId)
            ->where('account_id', $accountId)
            ->get();

        return $result;
    }

    /**
     * Save node data to DB
     *
     * @param Node $node
     * @param array $inputs
     * @return Node
     */
    public function saveNode(Node $node, $inputs)
    {
        $node->integration_id = $inputs['integration_id'];
        $node->application_id = $inputs['application_id'] ?? null;
        $node->application_type = $inputs['application_type'] ?? null;
        $node->account_id = !isset($inputs['account_id']) ? null : $inputs['account_id'];
        $node->ordering = $inputs['ordering'];
        $node->save();

        return $node;
    }

    /**
     * Create a new node
     *
     * @param array $inputs
     * @return Node
     */
    public function store($inputs)
    {
        $node = $this->saveNode(new $this->model, $inputs);

        return $node;
    }

    public function getCountOfWays(Application $application, int $userId)
    {
        return $this->model
            ->fromSub(function($query) use ($userId, $application) {
                $query->select('integration_id', 'application_id')
                    ->from('integration_nodes')
                    ->join('integrations', 'integrations.id', '=', 'integration_nodes.integration_id')
                    ->where('integrations.user_id', $userId)
                    ->where("application_id", $application->id)->distinct();
            }, 'unique_records')
            ->count();
    }

    /**
     * Update node
     *
     * @param array $inputs
     * @param Node $node
     * @return Node
     */
    public function update($inputs, Node $node)
    {
        $node = $this->saveNode($node, $inputs);

        return $node;
    }

    /**
     * Find last ordering from integration
     *
     * @param int $integrationId
     * @return int
     */
    public function lastOrderingForIntegration($integrationId)
    {
        $ordering = $this->model->where('integration_id', $integrationId)->max('ordering');
        return empty($ordering) ? 0 : $ordering;
    }

    /**
     * Get previous node using ordering field
     *
     * @param Node $node
     * @return Node|null
     */
    public function getPreviousNode(Node $node)
    {
        if($node->ordering == 1) {
            return null;
        }
        $previousNode = $this->model
            ->where([
                'integration_id' => $node->getAttribute('integration_id'),
                'ordering' => $node->getAttribute('ordering') - 1,
            ])
            ->first();

        return $previousNode;
    }

    /**
     * Get next node using ordering field
     *
     * @param Node $node
     * @return Node|null
     */
    public function getNextNode(Node $node)
    {
        $nextNode = $this->model
            ->where([
                'integration_id' => $node->getAttribute('integration_id'),
                'ordering' => $node->getAttribute('ordering') + 1,
            ])
            ->first();

        return $nextNode;
    }

    /**
     * Recalculate order for nodes in the integration
     *
     * @param Integration $integration
     * @return bool
     */
    public function recalculateOrder(Integration $integration)
    {
        $nodes = $integration->nodes()->get();
        if(!$nodes)
            return false;

        foreach ($nodes as $key => $node) {
            $node->ordering = $key + 1;
            $node->save();
        }
        return true;
    }

    /**
     * Delete node
     *
     * @param Node $node
     */
    public function delete(Node $node)
    {
        $node->delete();
    }

    /**
     * Destroy node completely
     *
     * @param Node $node
     */
    public function destroy(Node $node)
    {
        $this->deleteApplicationDependentData($node, true);
        $this->delete($node);
    }

    /**
     * @param Node $node
     * @param bool $resetApplication
     * @return Node
     */
    public function deleteApplicationDependentData(Node $node, $resetApplication = false)
    {
        // Load proper application node repository and remove all necessary data
        if(!empty($node->application)) {
            $appNodeRepository = app()->makeWith(NodeRepositoryContract::class, ['node' => $node]);
            $appNodeRepository->deleteAllData($node);
        }

        if($resetApplication) {
            $node->application_id = null;
            $node->application_type = null;
            $node->account_id = null;
            $node->save();
        }

        return $node;
    }


}
