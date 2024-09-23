<?php

namespace Modules\Instagram\Http\Resources\ActionNode;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FieldCollection extends ResourceCollection
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
            return (new FieldResource($field));
        });

        return parent::toArray($request);
    }
}
