<?php

namespace Modules\Instagram\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Apiway\InputsDesigner\Contracts\InputField;

class NodeField extends Model implements InputField
{
    const TABLE_NAME = 'instagram_node_fields';

    protected $table = self::TABLE_NAME;

    protected $fillable = ['node_id', 'identifier', 'title', 'type', 'required', 'example_value', 'dynamic', 'uses_fields', 'description',
        'position', 'custom_field', 'ordering', 'loader', 'dropdown_source', 'parent_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    /**
     * @param Builder $query
     */
    public function scopeParent(Builder $query)
    {
        $query->whereNull('parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, 'parent_id');
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
}
