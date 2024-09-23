<?php

namespace Modules\Instagram\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Instagram\Entities\Action;

class ActionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (Action $action) {
            return (new ActionResource($action));
        });

        return parent::toArray($request);
    }
}
