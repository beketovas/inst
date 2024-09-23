<?php

namespace Modules\Integration\Storage;

use App\Traits\CacheBuilder;
use Modules\Integration\Contracts\ApplicationNode;
use Modules\Integration\Entities\Integration as IntegrationEntity;
use Modules\Integration\Entities\Node;
use Modules\Integration\Exceptions\NodeException;
use Modules\Application\Entities\Application;


class Integration
{

    use CacheBuilder;

    /**
     * @var IntegrationEntity
     */
    protected $integration;

    /**
     * @var Node
     */
    public $triggerNode;

    /**
     * @var ApplicationNode
     */
    public $triggerAppNode;

    /**
     * @var Application
     */
    public $triggerNodeApplication;

    /**
     * @var Node
     */
    public $actionNode;

    /**
     * @var ApplicationNode
     */
    public $actionAppNode;

    /**
     * @var Application
     */
    public $actionNodeApplication;

    /**
     * Integration constructor.
     * @param IntegrationEntity $integration
     * @throws NodeException
     */
    public function __construct(IntegrationEntity $integration)
    {
        $this->integration = $integration;

        $this->getData();
    }

    /**
     * @return string
     */
    protected function cacheKey()
    {
        return 'integration_'.$this->integration->id.'_sync';
    }

    /**
     * Prepare all needed data
     *
     * @throws NodeException
     */
    public function getData()
    {
        $this->triggerNode = $this->cacheRemember([$this->cacheKey(), 'triggerNode'], function() {
            return $this->integration->triggerNode();
        });

        if(isset($this->triggerNode->application_type)) {
            $triggerNodeMapper = $this->triggerNode->nodeMapper($this->triggerNode->application_type);
            $this->triggerAppNode = $triggerNodeMapper->applicationNode();
            $this->triggerNodeApplication = $triggerNodeMapper->application();
        }

        $this->actionNode = $this->cacheRemember([$this->cacheKey(), 'actionNode'], function() {
            return $this->integration->actionNode();
        });

        if(isset($this->actionNode->application_type)) {
            $actionNodeMapper = $this->actionNode->nodeMapper($this->actionNode->application_type);
            $this->actionAppNode = $actionNodeMapper->applicationNode();
            $this->actionNodeApplication = $actionNodeMapper->application();
        }
    }

    public function getIntegration(): IntegrationEntity
    {
        return $this->integration;
    }

}
