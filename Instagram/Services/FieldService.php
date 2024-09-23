<?php

namespace Modules\Instagram\Services;

use Apiway\ServicesDataStorage\DataStorage;
use Modules\Integration\Contracts\ApplicationNode;
use Modules\Instagram\Adapters\DataStorageAdapter;
use Modules\Instagram\Exceptions\FieldServiceException;
use Modules\Instagram\Entities\Node;

class FieldService
{

    /**
     * @var array
     */
    protected $webhookData;

    /**
     * @var Node
     */
    protected $node;

    /**
     * Service constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param array $webhookData
     */
    public function setWebhookData($webhookData)
    {
        $this->webhookData = $webhookData;
    }

    /**
     * @return array
     */
    public function getWebhookData()
    {
        return $this->webhookData;
    }


    /**
     * @param ApplicationNode $node
     */
    public function setNode(ApplicationNode $node)
    {
        $this->node = $node;
    }

    /**
     * @return Node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Transform fields from webhook to be saved to db
     *
     * @return DataStorage
     * @throws FieldServiceException
     */
    public function prepareFields()
    {
        if(!$this->webhookData || !$this->node)
            throw new FieldServiceException('Field service needs to have webhook data and node to prepare fields.');

        $dataStorage = (new DataStorageAdapter($this->webhookData))->transform();
        return $dataStorage;
    }
}
