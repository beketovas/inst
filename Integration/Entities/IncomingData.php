<?php

namespace Modules\Integration\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Apiway\IncomingData\Entities\MySQL\IncomingData as BaseIncomingData;

class IncomingData extends BaseIncomingData
{
    /**
     * @return BelongsTo
     */
    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }
}
