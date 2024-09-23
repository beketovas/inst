<?php

namespace Modules\Instagram\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NodeResource extends JsonResource
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
            'node_id' => $this->node_id,
            'action_id' => $this->action_id,
        ];
    }
}
