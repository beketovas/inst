<?php

namespace Modules\Integration\Events;

use Modules\Integration\Entities\Node;
use Illuminate\Queue\SerializesModels;

class BeforeApplicationChange
{
    use SerializesModels;

    public $node;

    /**
     * Create a new event instance.
     *
     * @param Node $node
     */
    public function __construct(Node $node)
    {
        $this->node = $node;
    }
}