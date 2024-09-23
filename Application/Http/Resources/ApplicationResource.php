<?php

namespace Modules\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Modules\Application\Facades\ApplicationRepository;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->iconUrl,
            'type' => $this->type,
            'active' => $this->active,
            'beta' => $this->beta,
            'ordering' => $this->ordering,
            'connected' => $this->connected,
        ];
        if(!\request()->routeIs('integrations.*'))
            $data['soft'] = ApplicationRepository::getApplicationSoft($this->resource);
        return $data;
    }
}
