<?php

namespace Modules\Instagram\Entities;

use Illuminate\Database\Eloquent\Model;
use Apiway\InputsDesigner\Contracts\InputFieldValue;

class FieldValue extends Model implements InputFieldValue
{
    const TABLE_NAME = 'instagram_field_values';

    protected $table = self::TABLE_NAME;

    protected $fillable = ['node_id', 'field_id', 'value', 'value_json', 'additional_data'];

    protected $casts = [
        'value_json' => 'array',
        'additional_data' => 'array'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    /**
     * @return string|null
     */
    public function getValueFactualAttribute()
    {
        if(!is_null($this->value))
            return $this->value;

        if(is_null($this->value_json['value']))
            return null;

        return strval($this->value_json['value']);
    }
}
