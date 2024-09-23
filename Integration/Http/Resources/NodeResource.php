<?php

namespace Modules\Integration\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use Modules\Application\Http\Resources\ApplicationResource;

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
        $application = $this->application;

        $entityResource = new NodeEntityResource($this->entity);
        $integrationResource = new IntegrationResource($this->integration);
        $applicationResource = $application ? new ApplicationResource($application) : null;
        $appCollectionResource = $this->available_applications ? ApplicationResource::collection($this->available_applications) : null;

        $applicationDataResourceClass = null;
        if($application) {
            $applicationDataResourceClass = 'Modules\\' . studly_case($application->type) . '\\Http\\Resources\\';
            if($this->is_trigger) {
                $applicationDataResourceClass .= 'TriggerNode\\';
            } else {
                $applicationDataResourceClass .= 'ActionNode\\';
            }
            $applicationDataResourceClass .= 'DataResource';
        }
        $applicationDataResource = ($this->application_data && class_exists($applicationDataResourceClass)) ? new $applicationDataResourceClass($this->application_data) : null;

        return [
            'integration' => $integrationResource->toArray($this->integration),
            'name' => $this->name,
            'entity' => $entityResource->toArray($this->entity),
            'is_trigger' => $this->is_trigger,
            'user_id' => $this->user->id,
            'application' => $applicationResource ? $applicationResource->toArray($application) : null,
            'application_id' => $application ? $application->id : null,
            'available_applications' => $appCollectionResource ? $appCollectionResource->toArray($this->available_applications) : null,
            'integrations_link' => route('integrations'),
            'application_data' => $applicationDataResource ? $applicationDataResource->toArray($this->application_data) : null, // dynamic data structure depends on selected application
        ];

    }
}
