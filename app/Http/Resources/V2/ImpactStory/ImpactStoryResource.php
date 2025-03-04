<?php

namespace App\Http\Resources\V2\ImpactStory;

use App\Models\V2\WorldCountryGeneralized;
use Illuminate\Http\Resources\Json\JsonResource;

class ImpactStoryResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'status' => $this->status,
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'uuid' => $this->organization->uuid,
                    'name' => $this->organization->name,
                    'web_url' => $this->organization->web_url,
                    'facebook_url' => $this->organization->facebook_url,
                    'instagram_url' => $this->organization->instagram_url,
                    'linkedin_url' => $this->organization->linkedin_url,
                    'twitter_url' => $this->organization->twitter_url,
'countries' => ! empty($this->organization->countries)
    ? collect(WorldCountryGeneralized::whereIn('iso', (array) $this->organization->countries)->get())
        ->map(function ($country) {
            return [
              'label' => $country->country ?? null,
              'icon' => isset($country->iso) ? '/flags/' . strtolower($country->iso) . '.svg' : null,
            ];
        })
        ->filter()
        ->values()
        ->toArray()
    : [],
                ];
            }),
            'date' => $this->date,
            'category' => $this->category,
            'thumbnail' => $this->thumbnail,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $this->appendFilesToResource($data);
    }
}
