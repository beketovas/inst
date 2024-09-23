<?php

namespace Modules\Instagram\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Integration\Entities\Integration;
use Apiway\Hooks\Contracts\Hook;

class Webhook extends Model implements Hook
{
    protected $table = 'instagram_node_webhooks';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function webhookData()
    {
        return $this->hasMany(WebhookData::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
