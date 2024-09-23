<?php

namespace Modules\Instagram\Entities;

use App\Traits\CacheBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Application\Entities\Account;
use Modules\Integration\Entities\Integration;
use Apiway\Hooks\Contracts\Hook;

class Subscription extends Model implements Hook
{
    use CacheBuilder;

    protected $table = 'instagram_node_subscriptions';

    protected $fillable = ['node_id', 'account_id', 'page_id', 'form_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function flushCache()
    {
        $this->cacheFlushTags("instagram_subscription_{$this->page_id}_{$this->form_id}");
        $this->cacheFlushTags('instagram_subscription_'.$this->id);
    }
}
