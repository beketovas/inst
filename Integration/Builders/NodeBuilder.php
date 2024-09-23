<?php

namespace Modules\Integration\Builders;

use Illuminate\Support\Collection;
use Modules\Integration\Exceptions\NodeBuildingException;
use Modules\Integration\Models\Node as NodeModel;
use Modules\Integration\Entities\Node;

use Modules\Integration\Builders\Node\TriggerBuilder;
use Modules\Integration\Builders\Node\ActionBuilder;

use Modules\Integration\Contracts\NodeBuilderContract;

use Modules\Integration\Http\Resources\NodeResource as NodeResource;

class NodeBuilder implements NodeBuilderContract
{

    protected $nodeModel;

    protected $triggerBuilder;
    protected $actionBuilder;

    protected $nodes;

    public function __construct(TriggerBuilder $triggerBuilder, ActionBuilder $actionBuilder)
    {
        $this->triggerBuilder = $triggerBuilder;
        $this->actionBuilder = $actionBuilder;
    }

    public function setNodeModel(NodeModel $nodeModel)
    {
        $this->nodeModel = $nodeModel;
    }

    public function setNodes(Collection $nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * @param Node $node
     * @throws NodeBuildingException
     */
    public function build(Node $node)
    {
        if($node->isTrigger()) {
            $this->triggerBuilder->setNodes($this->nodes);
            $this->triggerBuilder->build($node);
            $this->nodeModel = $this->triggerBuilder->getNode();
        } else {
            $this->actionBuilder->setNodes($this->nodes);
            $this->actionBuilder->build($node, $this->nodeModel); // add also trigger node model
            $this->nodeModel = $this->actionBuilder->getNode();
        }

    }

    public function getNode()
    {
        return $this->nodeModel;
    }

    public function getNodeArray()
    {
        $resource = new NodeResource($this->nodeModel);
        return $resource->toArray($this->nodeModel);
    }


}
