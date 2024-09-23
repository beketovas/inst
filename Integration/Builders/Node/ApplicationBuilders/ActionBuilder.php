<?php
namespace Modules\Integration\Builders\Node\ApplicationBuilders;

use App\Traits\CacheBuilder;
use Apiway\InputsDesigner\Contracts\Repository\FieldWithValues as FieldWithValuesRepository;
use Apiway\InputsDesigner\Contracts\Repository\InputField as InputFieldRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Modules\Application\Contracts\ActionRepositoryContract;
use Modules\Application\Contracts\NodeModelContract;
use Modules\Application\Facades\ApplicationRepository;
use Modules\Integration\Models\Node as NodeModel;
use Modules\Integration\Contracts\NodeBuilderContract;

class ActionBuilder implements NodeBuilderContract
{
    use CacheBuilder;

    protected NodeModel $nodeModel;

    protected NodeModelContract $appNodeModel;

    protected ActionRepositoryContract $actionRepository;

    protected InputFieldRepository $nodeFieldRepository;

    protected $triggerNode;

    /**
     * ActionBuilder constructor.
     *
     * @param NodeModel $nodeModel
     * @throws BindingResolutionException
     */
    public function __construct(NodeModel $nodeModel)
    {
        $this->nodeModel = $nodeModel;
        $appType = $nodeModel->application->type;
        $this->appNodeModel = $this->appNodeModel = app(NodeModel::class);
        $this->actionRepository = app()->make('Modules\\'.studly_case($appType).'\\Repositories\\ActionRepository');
        $this->nodeFieldRepository = app()->make('Modules\\'.studly_case($appType).'\\Repositories\\NodeFieldRepository');
    }

    public function getTriggerNode()
    {
        return $this->triggerNode;
    }

    public function setTriggerNode($triggerNode)
    {
        $this->triggerNode = $triggerNode;
    }

    public function setAccount()
    {
        $userId = $this->nodeModel->user->id;
        $application = $this->nodeModel->application;

        if(empty($application)) {
            $this->appNodeModel->setAttribute('account', null);
        } else {
            $account = $this->cacheRemember(
                ['node_'.$this->nodeModel->entity->id, $application->type.'_account_user_'.$userId],
                function() use ($userId, $application) {
                    return ApplicationRepository::getApplicationAccountByUserId($application->type, $userId);
                }
            );
            $this->appNodeModel->setAttribute('account', $account);
        }
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

    public function setAvailableActions()
    {
        $application = $this->nodeModel->application;
        if(empty($application)) {
            $this->appNodeModel->setAttribute('available_actions', null);
        } else {
            $availableActions = $this->cacheRemember(['node_'.$this->nodeModel->entity->id, 'available_actions'],
                function() {
                    return $this->actionRepository->getForAction();
                }
            );
            $this->appNodeModel->setAttribute('available_actions', $availableActions);
        }
    }

    public function setFields()
    {
        $fields = $this->cacheRemember(['node_'.$this->nodeModel->entity->id, 'fields'],
            function() {
                return $this->nodeFieldRepository->getFieldsByNodeWithValues($this->appNodeModel->app_node);
            }
        );
        if($fields) {
            $this->appNodeModel->setAttribute('fields', $fields);
        } else {
            $this->appNodeModel->setAttribute('fields', []);
        }
    }

    public function build()
    {
        $this->setAccount();
        $this->setApplicationNode();
        $this->setAction();
        $this->setAvailableActions();
        $this->setFields();
    }

    public function getAppNode()
    {
        return $this->appNodeModel;
    }

    public function getNode()
    {
        return $this->nodeModel;
    }


}
