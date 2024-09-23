<?php

namespace Modules\Instagram\Entities;

use Illuminate\Database\Eloquent\Model;
use Apiway\InputsDesigner\Contracts\InputField;

class Field extends Model implements InputField
{
    const TABLE_NAME = 'instagram_fields';

    protected $table = self::TABLE_NAME;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function action()
    {
        return $this->belongsTo(Action::class);
    }

    /**
     * @return string|null
     */
    public function getValueFactualAttribute()
    {
        if(!is_null($this->value))
            return $this->value;

        if(is_null($this->value_json))
            return null;

        $valueJson = json_decode($this->value_json, true);
        if(is_null($valueJson['value']))
            return null;

        return strval($valueJson['value']);
    }

    /**
     * @return array|null
     */
    public function getUsesFieldsArrayAttribute()
    {
        if(!$this->uses_fields)
            return null;

        return json_decode($this->uses_fields, true);
    }

    /**
     * @return array
     */
    public function getAdditionalDataArrayAttribute()
    {
        if(!$this->additional_data)
            return [];

        return json_decode($this->additional_data, true);
    }
}
