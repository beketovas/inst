<?php

namespace Modules\Integration\Mappers;

use App\Traits\CacheBuilder;
use Modules\Integration\Entities\Node as NodeEntity;

abstract class Field
{
    use CacheBuilder;

    protected $node;

    public function __construct(NodeEntity $node)
    {
        $this->node = $node;
    }

    /**
     * @return string
     */
    protected function cacheKey()
    {
        return 'node_storage_'.$this->node->id;
    }
}
