<?php

namespace Modules\Integration\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Integration\Entities\Node;

class ActionChanged
{
    use SerializesModels;

    /**
     * @var Node
     */
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
