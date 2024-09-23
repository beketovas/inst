<?php

namespace Modules\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Modules\Application\Facades\ApplicationRepository;

class UserApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $preparedArray = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->iconUrl,
            'type' => $this->type,
            'active' => $this->active,
            'beta' => $this->beta,
            'ordering' => $this->ordering,
            'account' => null,
            'soft' => ApplicationRepository::getApplicationSoft($this->resource),
            'count_of_ways' => $this->count_of_ways
        ];
        if(isset($this->account)) {
            $preparedArray['account'] = $this->account;
            $diff = now()->diffInWeeks($this->account->created_at) . ' weeks ago';
            if ($diff == 0) {
                $diff = now()->diffInDays($this->account->created_at) . ' days ago';
                if ($diff == 0)
                    $diff = ' recently';
            } else if ($diff > 52)
                $diff = now()->diffInYears($this->account->created_at) . ' years ago';
            $preparedArray['diff_date'] = $diff;
        }
        return $preparedArray;
    }
}
