<?php declare(strict_types=1);

namespace Modules\Integration\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Application\Http\Resources\ApplicationResource;

class SearchIntegrationResource extends JsonResource
{
    public function toArray($request)
    {
        $preparedArray = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'code' => $this->code,
            'name' => 'Way name',
            'active' => $this->active,
            'trigger_application' => null,
            'action_application' => null
        ];

        if(isset($this->name))
            $preparedArray['name'] = $this->name;

        if(!isset($this->nodes[0]->application))
            return $preparedArray;

        $preparedArray['trigger_application'] = ApplicationResource::make($this->nodes[0]->application);

        if(!isset($this->nodes[1]->application))
            return $preparedArray;

        $preparedArray['action_application'] = ApplicationResource::make($this->nodes[1]->application);
        return $preparedArray;
    }
}
