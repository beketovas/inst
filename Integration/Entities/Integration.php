<?php

namespace Modules\Integration\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Integration\Helpers\IntegrationHelper;
use App\Traits\CacheBuilder;

class Integration extends Model
{

    use CacheBuilder;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function nodes()
    {
        return $this->hasMany('Modules\Integration\Entities\Node');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('Modules\User\Entities\User');
    }

    /**
     * Find a trigger node for the integration, that is in fact the first node
     *
     * @return Node || null
     */
    public function triggerNode()
    {
        return $this->nodes->where('ordering', 1)->first();
    }

    /**
     * Find an action node for the integration, that is in fact the second node
     *
     * @return Node || null
     */
    public function actionNode()
    {
        return $this->nodes->where('ordering', 2)->first();
    }

    /**
     * Find the last node for the integration
     *
     * @return Node || null
     */
    public function lastNode()
    {
        $lastOrdering = $this->nodes()->max('ordering');
        return $this->nodes()->where('ordering', $lastOrdering)->first();
    }

    public function getTitle()
    {
        return IntegrationHelper::getTitle($this);
    }

    /**
     * Is integration ready for activation
     *
     * @return bool
     */
    public function readyForActivation()
    {
        $nodes = $this->nodes;
        // If integration has less than 2 nodes
        if(empty($nodes) || count($nodes) < 2)
            return false;

        foreach ($nodes as $node) {
            // If at least one node is not completed
            if(!$node->isCompleted()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Flush all integration cache
     */
    public function flushCache()
    {
        $this->cacheFlushTags('integration_' . $this->id . '_sync');
        $this->cacheFlushTags('integrations_user_'.$this->user_id);
        if($this->nodes) {
            $this->nodes->each(function(Node $node, $key) {
                $node->flushCache();
            });
        }
        $this->cacheFlushTags('integration_'.$this->id);
        $this->cacheForget(['integrations', 'integration_code_'.$this->code], ['integrations', 'integration_id_'.$this->id]);
    }
}
