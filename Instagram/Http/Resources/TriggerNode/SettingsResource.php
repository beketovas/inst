<?php

namespace Modules\Instagram\Http\Resources\TriggerNode;

use Apiway\InputsDesigner\Http\Resources\Dropdown\ValueJsonResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
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
            'action_id' => $this->action_id,
            'identifier' => $this->identifier,
            'title' => $this->title,
            'type' => $this->type,
            'required' => $this->required,
            'value' => $this->value,
            'value_json' => $fieldValueJson ? $fieldValueJson->toArray($this->value_json) : ['value' => '', 'label' => ''],
            'has_value' => $this->value || $fieldValueJson,
            'uses_fields' => $this->uses_fields,
        ];
    }
}
