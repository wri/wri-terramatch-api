<?php

namespace App\Http\Resources\V2\ImpactStory;

use App\Models\V2\WorldCountryGeneralized;
use Illuminate\Http\Resources\Json\JsonResource;

class ImpactStoryLiteResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'status' => $this->status,
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'name' => $this->organization->name,
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
            'created_at' => $this->created_at,
        ];

        return $this->appendFilesToResource($data);
    }
}