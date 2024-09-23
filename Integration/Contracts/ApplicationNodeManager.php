<?php
namespace Modules\Integration\Contracts;

use Apiway\IncomingData\Contracts\IncomingDataEntity;
use Modules\Integration\Entities\Node;

/**
 * @method Sender createSender(Node $node, IncomingDataEntity $data)
 * @method createExecutor(Node $node)
 */
interface ApplicationNodeManager
{

}
