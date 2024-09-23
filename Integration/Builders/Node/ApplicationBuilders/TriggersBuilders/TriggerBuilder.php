<?php

namespace Modules\Integration\Builders\Node\ApplicationBuilders\TriggersBuilders;

use Apiway\InputsDesigner\Contracts\Repository\FieldWithValues as FieldWithValuesRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Modules\Application\Facades\ApplicationRepository;
use Modules\ContentManagement\Entities\Log;
use Modules\Integration\Models\Node as NodeModel;
use Modules\Integration\Exceptions\NodeBuildingException;

class TriggerBuilder extends AbstractTriggerBuilder
{
    protected FieldWithValuesRepository $fieldRepository;

    /**
     * @var Collection
     */
    protected $nodes;

    /**
     * TriggerBuilder constructor.
     * @param NodeModel $nodeModel
     * @throws BindingResolutionException
     */
    public function __construct(NodeModel $nodeModel)
    {
        $appType = $nodeModel->application->type;
        if(class_exists('Modules\\'.studly_case($appType).'\\Repositories\\FieldRepository'))
            $this->fieldRepository = app()->make('Modules\\'.studly_case($appType).'\\Repositories\\FieldRepository');

        parent::__construct($nodeModel);
    }

    /**
     * @throws NodeBuildingException
     */
    public function setAccount()
    {
        $userId = $this->nodeModel->user->id;
        $application = $this->nodeModel->application;
        if(empty($application)) {
            throw new NodeBuildingException("Application must be selected before setting account.");
        }
        $account = $this->cacheRemember(
            ['node_'.$this->nodeModel->entity->id, $application->type.'_account_user_'.$userId],
            function() use ($userId, $application) {
                return ApplicationRepository::getApplicationAccountByUserId($application->type, $userId);
            }
        );
        $this->appNodeModel->setAttribute('account', $account);
    }

    public function setAvailableActions()
    {
        $availableActions = $this->cacheRemember(['node_'.$this->nodeModel->entity->id, 'available_actions'],
            function() {
                return $this->actionRepository->getForTrigger();
            }
        );
        $this->appNodeModel->setAttribute('available_actions', $availableActions);
    }

    public function setSettings()
    {
        if($this->appNodeModel->action) {
            $settings = $this->cacheRemember(['node_'.$this->nodeModel->entity->id, 'settings'],
                function() {
                    return $this->fieldRepository->getWithValues([
                        'actionId' => $this->appNodeModel->action->id,
                        'appNodeId' => $this->nodeModel->entity->applicationNode->id
                    ]);
                }
            );
            $this->appNodeModel->setAttribute('settings', $settings);
        } else {
            $this->appNodeModel->setAttribute('settings', []);
        }
    }

    /**
     * @throws NodeBuildingException
     */
    public function build()
    {
        $this->setApplicationNode();
        $this->setAccount();
        $this->setAction();
        $this->setAvailableActions();
        if(isset($this->fieldRepository))
            $this->setSettings();
        $this->setFields();
    }
}
