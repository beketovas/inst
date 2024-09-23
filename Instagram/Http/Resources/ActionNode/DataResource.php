<?php

namespace Modules\Instagram\Http\Resources\ActionNode;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Instagram\Http\Resources\AccountResource;
use Modules\Instagram\Http\Resources\NodeResource;
use Modules\Instagram\Http\Resources\ActionResource;
use Modules\Instagram\Http\Resources\ActionCollection;

class DataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $accountResource = $this->account ? new AccountResource($this->account) : null;
        $appNodeResource = $this->app_node ? new NodeResource($this->app_node) : null;
        $actionResource = $this->action ? new ActionResource($this->action) : null;
        $actionCollectionResource = $this->available_actions ? ActionResource::collection($this->available_actions) : null;
        $fieldCollection = $this->fields ? FieldResource::collection($this->fields) : null;

        return [
            'account' => $accountResource ? $accountResource->toArray($this->account) : null,
            'app_node' => $appNodeResource ? $appNodeResource->toArray($this->app_node) : null,
            'action' => $actionResource ? $actionResource->toArray($this->action) : null,
            'available_actions' => $actionCollectionResource ? $actionCollectionResource->toArray($this->available_actions) : null,
            'available_actions_select_text' => __('integration::node.choose_action'),
            'fields' => $fieldCollection ? $fieldCollection->toArray($this->fields) : null,
        ];

    }
}
