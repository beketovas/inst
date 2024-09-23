<?php
namespace Modules\Integration\Builders\Node;

use Illuminate\Support\Collection;
use Modules\Integration\Models\Node as NodeModel;
use Modules\Integration\Entities\Node;
use Modules\Application\Contracts\NodeModelContract;
use Modules\Application\Repositories\ApplicationRepository;
use Modules\Integration\Contracts\NodeBuilderContract;
use Modules\Integration\Exceptions\NodeBuildingException;

class ActionBuilder implements NodeBuilderContract
{
    protected $node;
    protected $nodeModel;
    protected $triggerNode;
    protected $applicationRepository;
    protected $nodes;

    public function __construct(NodeModel $node, ApplicationRepository $applicationRepository)
    {
        $this->nodeModel = $node;
        $this->applicationRepository = $applicationRepository;
    }

    public function setNodes(Collection $nodes)
    {
        $this->nodes = $nodes;
    }

    public function setName()
    {
        $this->nodeModel->setAttribute('name', __('integration::node.action'));
    }

    public function setEntity(Node $node)
    {
        $this->nodeModel->setAttribute('entity', $node);
    }

    public function setIntegration()
    {
        $this->nodeModel->setAttribute('integration',$this->nodeModel->entity->integration);
    }

    public function setUser()
    {
        $this->nodeModel->setAttribute('user', $this->nodeModel->entity->integration->user);
    }

    public function setIsTrigger()
    {
        $this->nodeModel->setAttribute('is_trigger', $this->nodeModel->entity->isTrigger());
    }

    public function setApplication()
    {
        $application = $this->nodeModel->entity->application;
        if(empty($application)) {
            $this->nodeModel->setAttribute('application', null);
        } else {
            $this->nodeModel->setAttribute('application', $application);
        }
    }

    /**
     * @throws NodeBuildingException
     */
    public function setAvailableApplications()
    {
        if(empty($this->nodeModel->user)) {
            throw new NodeBuildingException("User must be present before building available applications.");
        }
        // If trigger node has selected application, get related
        if($triggerApplication = $this->triggerNode->entity->application) {
            $relatedApplications = $this->applicationRepository->getForAction($this->nodeModel->user->id);
            $this->nodeModel->setAttribute('available_applications', $relatedApplications);
        } else { // if not, assign null
            $this->nodeModel->setAttribute('available_applications', null);
        }
    }

    /**
     * @throws NodeBuildingException
     */
    public function setApplicationData()
    {
        if(empty($this->nodeModel->entity)) {
            throw new NodeBuildingException("Entity must be present before building application node.");
        }
        if(empty($this->nodeModel->application)) {
            $this->nodeModel->setAttribute('application_data', null);
        } else {
            $applicationBuilder = app()->makeWith(ApplicationBuilders\ActionBuilder::class, ['nodeModel' => $this->nodeModel]);
            $applicationBuilder->setTriggerNode($this->triggerNode);
            $applicationBuilder->build();
            $this->nodeModel->setAttribute('application_data', $applicationBuilder->getAppNode());
        }
    }

    /**
     * @param Node $node
     * @param NodeModel $triggerModel
     * @throws NodeBuildingException
     */
    public function build(Node $node, NodeModel $triggerModel)
    {
        $this->triggerNode = $triggerModel;

        $this->setName();
        $this->setEntity($node);
        $this->setIntegration();
        $this->setUser();
        $this->setIsTrigger();
        $this->setApplication();
        $this->setAvailableApplications();
        $this->setApplicationData();

    }

    public function getNode()
    {
        return $this->nodeModel;
    }

    public function getNodeArray()
    {
        return $this->nodeModel;
    }


}
