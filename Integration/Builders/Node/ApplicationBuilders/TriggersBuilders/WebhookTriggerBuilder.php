<?php

namespace Modules\Integration\Builders\Node\ApplicationBuilders\TriggersBuilders;

use Illuminate\Support\Collection;
use Modules\Integration\Models\Node as NodeModel;
use Modules\Integration\Exceptions\NodeBuildingException;

class WebhookTriggerBuilder extends AbstractTriggerBuilder
{
    /**
     * @var Collection
     */
    protected $nodes;

    /**
     * TriggerBuilder constructor.
     * @param $appNodeModel
     * @param NodeModel $nodeModel
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(NodeModel $nodeModel)
    {
        parent::__construct($nodeModel);
    }

    public function setWebhook()
    {
        if(empty($this->appNodeModel->app_node)) {
            throw new NodeBuildingException("Application needs to have its own node before setting webhook data.");
        }
        $webhook = $this->cacheRemember(['node_'.$this->nodeModel->entity->id, 'webhook'],
            function() {
                return $this->appNodeModel->app_node->webhook;
            }
        );
        if($webhook) {
            $this->appNodeModel->setAttribute('webhook', $webhook);
            $this->appNodeModel->setAttribute('webhook_link', route("webhooks.catch.{$this->nodeModel->application->slug}",[$webhook->code]));
        } else {
            $this->appNodeModel->setAttribute('webhook', null);
            $this->appNodeModel->setAttribute('webhook_link', null);
        }
    }

    public function setAvailableActions()
    {
        $application = $this->nodeModel->application;
        if(empty($application)) {
            $this->appNodeModel->setAttribute('available_actions', null);
        } else {
            $actions = $this->cacheRemember(['node_'.$this->nodeModel->entity->id, 'available_actions'],
                function () use ($application) {
                    return $application->actions();
                }
            );
            $this->appNodeModel->setAttribute('available_actions', $actions);
        }
    }

    /**
     * @throws NodeBuildingException
     */
    public function build()
    {
        $this->setApplicationNode();
        $this->setWebhook();
        $this->setAction();
        $this->setAvailableActions();
        $this->setFields();
    }
}
