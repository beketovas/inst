<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;

class ApplicationRelationship extends Model
{
    protected $table = 'application_relationship';

    public function applicationMain()
    {
        return $this->belongsTo('Modules\Application\Entities\Application','application_id_1');
    }

    public function applicationRelated()
    {
        return $this->belongsTo('Modules\Application\Entities\Application','application_id_2');
    }
}
