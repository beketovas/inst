<?php

namespace Modules\Instagram\Repositories;

use Exception;
use Illuminate\Support\Collection;
use Modules\Integration\Entities\Node as BaseNode;
use Modules\Application\Contracts\NodeRepositoryContract;
use Modules\Instagram\Entities\Node;
use Modules\Instagram\Exceptions\NodeRepositoryException;

class NodeRepository implements NodeRepositoryContract
{
    /**
     * @var Node
     */
    protected $model;

    /**
     * @var FieldValueRepository
     */
    protected $fieldValueRepository;

    /**
     * @var NodeFieldRepository
     */
    protected $nodeFieldRepository;

    /**
     * NodeRepository constructor.
     *
     * @param Node $node
     * @param FieldValueRepository $fieldValueRepository
     * @param NodeFieldRepository $nodeFieldRepository
     */
    public function __construct(
        Node $node,
        FieldValueRepository $fieldValueRepository,
        NodeFieldRepository $nodeFieldRepository)
    {
        $this->model = $node;
        $this->fieldValueRepository = $fieldValueRepository;
        $this->nodeFieldRepository = $nodeFieldRepository;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return Node
     */
    public function getById($id)
    {
        $node = $this->model->find($id);
        return $node;
    }

    /**
     * Create a new node
     *
     * @param array $data
     * @return Node
     */
    public function store($data)
    {
        $node = $this->model->create($data);

        return $node;
    }

    /**
     * Update node
     *
     * @param array $data
     * @param Node $node
     * @return Node
     */
    public function update($data, Node $node)
    {
        $node->update($data);

        return $node;
    }

    /**
     * Delete node
     *
     * @param Node $node
     * @throws Exception
     */
    public function delete(Node $node)
    {
        $node->delete();
    }

    /**
     * @param BaseNode $baseNode
     * @param bool $resetAction
     * @throws NodeRepositoryException
     */
    public function deleteActionDependentData(BaseNode $baseNode, $resetAction = false)
    {
        $applicationNode = $baseNode->applicationNode;
        if(!$applicationNode) {
            throw new NodeRepositoryException('Application node does not exist.');
        }
        if($resetAction) {
            // Detach selected action action
            $applicationNode->action_id = null;
            $applicationNode->save();
        }

        $this->fieldValueRepository->deleteByNode($applicationNode->id);
        $this->nodeFieldRepository->deleteFieldsByFilter(['appNodeId' => $applicationNode->id]);
    }

    /**
     * @param BaseNode $baseNode
     * @return BaseNode
     * @throws Exception
     */
    public function deleteAllData(BaseNode $baseNode)
    {
        $applicationNode = $baseNode->applicationNode;

        // Delete node
        if($applicationNode) $this->delete($applicationNode);

        return $baseNode;
    }

    public function initNodeData(BaseNode $baseNode)
    {

    }
}
