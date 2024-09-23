<?php

namespace Modules\Instagram\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Instagram\Repositories\FieldRepository;
use Modules\Integration\Contracts\ApplicationNode;
use Modules\Integration\Contracts\ApplicationNodeManager;
use Modules\Integration\Entities\Node as BaseNode;
use Apiway\SynchronizationLog\Contracts\SynchronizationLogRepository;

class Node extends Model implements ApplicationNode
{
    const TABLE_NAME = 'instagram_nodes';

    protected $table = self::TABLE_NAME;

    protected $fillable = ['node_id', 'action_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function node()
    {
        return $this->belongsTo(BaseNode::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function action()
    {
        return $this->belongsTo(Action::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function webhook()
    {
        return $this->hasOne(Webhook::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * @return mixed|FieldRepository
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getFieldRepository()
    {
        return app()->make(FieldRepository::class);
    }

    /**
     * @return SynchronizationLogRepository|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getSynchronizationRepository()
    {
        return app()->make(SynchronizationLogRepository::class);
    }

    /**
     * @return ApplicationNodeManager
     */
    public function nodeManager() : ApplicationNodeManager
    {
        return app()->makeWith(ApplicationNodeManager::class, ['node' => $this]);
    }
}
