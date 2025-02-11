<?php

namespace App\Http\Resources\V2\ImpactStory;

use App\Models\V2\WorldCountryGeneralized;
use Illuminate\Http\Resources\Json\JsonResource;

class ImpactStoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'status' => $this->status,
            'organization' => $this->whenLoaded('organization', function () {
              return [
                'name' => $this->organization->name,
                'countries' => WorldCountryGeneralized::whereIn('iso', $this->organization->countries)
                    ->pluck('country')
                    ->toArray(),
              ];
            }),
            'date' => $this->date,
            'category' => $this->category,
            'thumbnail' => $this->thumbnail,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
