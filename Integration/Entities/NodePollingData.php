<?php

namespace Modules\Integration\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Apiway\NodePollingData\Entities\MySQL\NodePollingData as BasePollingData;

class NodePollingData extends BasePollingData
{
    /**
     * @return BelongsTo
     */
    public function subscription()
    {
        return $this->belongsTo(NodePolling::class, 'node_polling_id');
    }
}
