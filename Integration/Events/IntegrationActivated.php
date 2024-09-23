<?php

namespace Modules\Integration\Events;

use Modules\Integration\Entities\Node;

class IntegrationActivated
{
    public Node $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }
}
