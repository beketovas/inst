<?php

namespace Modules\Instagram\Http\Resources\ActionNode;

use Apiway\InputsDesigner\Http\Resources\Dropdown\ValueJsonResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FieldResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $fieldValueJson = !empty($this->value_json) ? new ValueJsonResource(json_decode($this->value_json)) : null;
        return [
            'id' => $this->id,
            'title' => $this->title,
            'identifier' => $this->identifier,
            'type' => $this->type,
            'required' => $this->required,
            'value' => $this->value,
            'marks' => $this->marks,
            'value_json' => $fieldValueJson ? $fieldValueJson->toArray($this->value_json) : ['value' => '', 'label' => ''],
            'has_value' => !is_null($this->value) || $fieldValueJson,
            'uses_fields' => $this->uses_fields,
            'description' => $this->description,
            'loader' => $this->loader,
            'available_related_fields' => $this->available_related_fields
        ];
    }
}
