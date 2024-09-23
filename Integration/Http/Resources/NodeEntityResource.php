<?php

namespace Modules\Integration\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NodeEntityResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'integration_id' => $this->integration_id,
            'ordering' => $this->ordering,
        ];
    }
}