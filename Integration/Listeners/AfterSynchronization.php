<?php

namespace Modules\Integration\Listeners;

use Apiway\IncomingData\Contracts\IncomingDataRepository;
use Modules\Integration\Events\SynchronizationCompleted;

class AfterSynchronization
{
    protected IncomingDataRepository $incomingDataRepository;

    public function __construct(IncomingDataRepository $incomingDataRepository)
    {
        $this->incomingDataRepository = $incomingDataRepository;
    }

    /**
     * Handle the event.
     *
     * @param SynchronizationCompleted $event
     * @return void
     */
    public function handle(SynchronizationCompleted $event)
    {
        $node = $event->node;
        $entity = $event->entity;
        $data = $event->data;
        $this->incomingDataRepository->makeProcessed($data);
    }
}
