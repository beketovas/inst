<?php

namespace Modules\Integration\Listeners;

use Modules\Integration\Events\IntegrationActivated;
use Modules\Integration\Facades\ProcessPolling;
use Modules\Integration\Repositories\NodePollingRepository;

class SyncPollingDataAfterActivation
{
    protected NodePollingRepository $nodePollingRepository;

    public function __construct(NodePollingRepository $nodePollingRepository)
    {
        $this->nodePollingRepository = $nodePollingRepository;
    }

    /**
     * @throws \Apiway\ApiRequest\Exceptions\UndefinedMethod
     * @throws \Modules\Integration\Exceptions\NodeException
     */
    public function handle(IntegrationActivated $integrationActivated)
    {
        $triggerNode = $integrationActivated->node;
        $nodePollings = $this->nodePollingRepository->getByNodeId($triggerNode->id);
        foreach ($nodePollings as $nodePolling) {
            $processPolling = ProcessPolling::create($nodePolling);
            $processPolling->process(true);
        }
    }
}
