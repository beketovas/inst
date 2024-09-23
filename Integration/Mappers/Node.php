<?php

namespace Modules\Integration\Mappers;

use App\Traits\CacheBuilder;
use Modules\Integration\Entities\Node as NodeEntity;

abstract class Node
{
    use CacheBuilder;

    protected $node;

    protected $nextNode;

    protected $previousNode;


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

    public function integration()
    {
        $integration = $this->cacheRemember(
            [$this->cacheKey(), 'integration'],
            function() {
                return $this->node->integration;
            }
        );

        return $integration;
    }

    /**
     * @return NodeEntity
     */
    public function previousNode()
    {
        $prevNode = $this->cacheRemember(
            [$this->cacheKey(), 'previous_node'],
            function() {
                return $this->node->previousNode();
            }
        );

        return $prevNode;
    }

    /**
     * @return NodeEntity
     */
    public function nextNode()
    {
        $nextNode = $this->cacheRemember(
            [$this->cacheKey(), 'next_node'],
            function() {
                return $this->node->nextNode();
            }
        );

        return $nextNode;
    }

    public function applicationNode()
    {
        return $this->cacheRemember(
            [$this->cacheKey(), 'application_node'],
            function() {
                return $this->node->applicationNode()->first();
            }
        );
    }

    public function application()
    {
        $application = $this->cacheRemember(
            [$this->cacheKey(), 'application'],
            function() {
                return $this->node->application;
            }
        );

        return $application;
    }

}
