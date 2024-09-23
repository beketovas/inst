<?php

namespace Modules\Integration\Listeners;

use Modules\Integration\Events\AfterApplicationChange;
use Modules\Integration\Repositories\NodeRepository;
use Modules\Application\Contracts\NodeRepositoryContract;
use Illuminate\Support\Facades\Log;

class AddApplicationNode
{

    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * Create the event listener.
     *
     * @param NodeRepository $nodeRepository
     */
    public function __construct(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * Handle the event.
     *
     * @param AfterApplicationChange $event
     * @return void
     */
    public function handle(AfterApplicationChange $event)
    {
        $node = $event->node;
        // Load proper application node repository
        $appNodeRepository = app()->makeWith(NodeRepositoryContract::class, ['node' => $node]);
        // Add application node
        $data = [
            'node_id' => $node->id
        ];
        $appNodeRepository->store($data);
    }
}