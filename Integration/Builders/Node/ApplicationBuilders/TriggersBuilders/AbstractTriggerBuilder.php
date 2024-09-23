<?php

namespace Modules\Integration\Builders\Node\ApplicationBuilders\TriggersBuilders;

use App\Traits\CacheBuilder;
use Apiway\InputsDesigner\Contracts\Repository\FieldWithValues as FieldWithValuesRepository;
use Apiway\InputsDesigner\Contracts\Repository\InputField as InputFieldRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Modules\Application\Contracts\ActionRepositoryContract;
use Modules\Application\Contracts\NodeModelContract;
use Modules\Integration\Models\Node as NodeModel;
use Modules\Integration\Contracts\NodeBuilderContract;
use Modules\Integration\Exceptions\NodeBuildingException;
use Modules\Integration\Services\CacheService;

abstract class AbstractTriggerBuilder implements NodeBuilderContract
{
    use CacheBuilder;

    protected NodeModel $nodeModel;

    protected NodeModelContract $appNodeModel;

    protected ActionRepositoryContract $actionRepository;

    protected InputFieldRepository $nodeFieldRepository;

    protected FieldWithValuesRepository $fieldRepository;

    /**
     * @var Collection
     */
    protected $nodes;

    /**
     * TriggerBuilder constructor.
     * @param NodeModel $nodeModel
     * @param CacheService $cacheService
     * @throws BindingResolutionException
     */
    public function __construct(NodeModel $nodeModel)
    {
        $this->nodeModel = $nodeModel;
        $appType = $nodeModel->application->type;
        $this->appNodeModel = app(NodeModel::class);
        if(class_exists($actionRepository = 'Modules\\'.studly_case($appType).'\\Repositories\\ActionRepository'))
            $this->actionRepository = app()->make($actionRepository);
        $this->nodeFieldRepository = app()->make('Modules\\'.studly_case($appType).'\\Repositories\\NodeFieldRepository');
    }

    public function setNodes(Collection $nodes)
    {
        $this->nodes = $nodes;
    }


    public function setApplicationNode()
    {
        $appNode = $this->cacheRemember(
            ['node_'.$this->nodeModel->entity->id, 'app_node'],
            function () {
                return $this->nodeModel->entity->applicationNode;
            }
        );
        $this->appNodeModel->setAttribute('app_node', $appNode);
    }

    public function setAction()
    {
        $action = $this->cacheRemember(
            ['node_'.$this->nodeModel->entity->id, 'action'],
            function () {
                return $this->appNodeModel->app_node->action;
            }
        );
        $this->appNodeModel->setAttribute('action', $action);
    }

    abstract public function setAvailableActions();

    public function setFields()
    {
        $fields = $this->cacheRemember(['node_'.$this->nodeModel->entity->id, 'fields'],
            function() {
                return $this->nodeFieldRepository->getAll([
                    'appNodeId' => $this->appNodeModel->app_node['id']
                ]);
            }
        );
        if($fields->isNotEmpty()) {
            $this->appNodeModel->setAttribute('fields', $fields);
        } else {
            $this->appNodeModel->setAttribute('fields', []);
        }
    }

    /**
     * @throws NodeBuildingException
     */
    abstract public function build();

    public function getAppNode()
    {
        return $this->appNodeModel;
    }

    public function getNode()
    {
        return $this->nodeModel;
    }


}
