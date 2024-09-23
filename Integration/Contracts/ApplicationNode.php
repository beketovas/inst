<?php
namespace Modules\Integration\Contracts;

use Modules\Integration\Contracts\ApplicationNodeManager;
use Modules\Integration\Entities\Node;

/**
 * @property int $id
 * @property Node $node
 * @property int $action_id
 */
interface ApplicationNode
{
    public function nodeManager() : ApplicationNodeManager;
}
