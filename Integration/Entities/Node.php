<?php

namespace Modules\Integration\Entities;

use App\Traits\CacheBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Application\Entities\Account;
use Modules\Integration\Contracts\BaseNode;
use Modules\Integration\Exceptions\NodeException;
use Modules\Integration\Contracts\NodeMapper;
use Modules\Application\Contracts\NodeErrorsBuilderContract;

class Node extends Model implements BaseNode
{
    use CacheBuilder;

    protected $table = 'integration_nodes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'integration_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function integration()
    {
        return $this->belongsTo('Modules\Integration\Entities\Integration');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function application()
    {
        return $this->belongsTo('Modules\Application\Entities\Application');
    }

    /**
     * @return HasOne
     */
    public function account()
    {
        if(class_exists('Modules\\'.studly_case($this->application_type).'\\Entities\\Account'))
            return $this->hasOne('Modules\\'.studly_case($this->application_type).'\\Entities\\Account', 'id', 'account_id');
        return $this->hasOne(Account::class, 'id', 'account_id');
    }

    /**
     * @return HasOne
     * @throws NodeException
     */
    public function applicationNode()
    {
        if(empty($this->application)) {
            throw new NodeException('Application must be selected to load application node. Node id: '. $this->id);
        }
        $class = 'Modules\\'.studly_case($this->application->type).'\\Entities\\Node';
        return $this->hasOne($class);
    }

    /**
     * @param string $appType
     * @return NodeMapper
     * @throws NodeException
     */
    public function nodeMapper(string $appType = ''): NodeMapper
    {
        if(!empty($appType)) {
            $facade = 'Modules\\'.studly_case($appType).'\\Facades\\NodeMapper';
            return $facade::load($this);
        } else {
            if (empty($this->application)) {
                throw new NodeException('Application must be selected to load node mapper.');
            }
            $facade = 'Modules\\' . studly_case($this->application->type) . '\\Facades\\NodeMapper';
            return $facade::load($this);
        }
    }

    public function nodePolling(): HasMany
    {
        return $this->hasMany(NodePolling::class, 'node_id');
    }

    /**
     * Get previous node
     * @return null|Node
     */
    public function previousNode()
    {
        if($this->getAttribute('ordering') == 1) {
            return null;
        }
        $previousNode = $this->where([
            'integration_id' => $this->getAttribute('integration_id'),
            'ordering' => $this->getAttribute('ordering') - 1,
        ])->first();

        return $previousNode;
    }

    /**
     * Get next node
     * @return null|Node
     */
    public function nextNode()
    {
        $nextNode = $this->where([
            'integration_id' => $this->getAttribute('integration_id'),
            'ordering' => $this->getAttribute('ordering') + 1,
        ])->first();

        return $nextNode;
    }

    /**
     * @return bool
     */
    public function isTrigger()
    {
        return ($this->ordering == 1) ? true : false;
    }

    /**
     * Is node completed
     *
     * @return bool
     */

    public function isCompleted()
    {
        $errorsBuilder = app()->makeWith(NodeErrorsBuilderContract::class, ['node' => $this]);
        $errorsBuilder->build();
        $errors = $errorsBuilder->getErrors();

        if($errors) return false;

        return true;
    }

    /**
     * Flush all integration cache
     */
    public function flushCache()
    {
        $this->cacheFlushTags('node_storage_' . $this->id);
        $this->cacheFlushTags('node_' . $this->id);
        $nodePollings = $this->nodePolling()->get();
        if(isset($nodePollings)) {
            $nodePollings->each(function ($item) {
                $item->flushCache();
            });
        }
    }

}
