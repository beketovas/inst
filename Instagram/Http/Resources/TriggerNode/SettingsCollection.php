<?php

namespace Modules\Instagram\Http\Resources\TriggerNode;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SettingsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function ($field) {
            return (new SettingsResource($field));
        });

        return parent::toArray($request);
    }
}
