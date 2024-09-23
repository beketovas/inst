<?php

namespace Modules\Application\Contracts;

use Modules\Integration\Entities\Node as BaseNode;

interface NodeRepositoryContract
{

    /**
     * Make necessary things after node creation
     *
     * @param BaseNode $baseNode
     */
    public function initNodeData(BaseNode $baseNode);

}