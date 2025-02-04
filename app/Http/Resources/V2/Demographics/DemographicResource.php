<?php

namespace App\Http\Resources\V2\Demographics;

use Illuminate\Http\Resources\Json\JsonResource;

class DemographicResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'collection' => $this->collection,
            'readable_collection' => $this->readable_collection,
            'demographics' => empty($this->entries) ? [] : DemographicEntryResource::collection($this->entries),
        ];
    }
}
