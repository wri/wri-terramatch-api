<?php

namespace App\Http\Resources\V2\Trackings;

use Illuminate\Http\Resources\Json\JsonResource;

class TrackingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'collection' => $this->collection,
            'entries' => empty($this->entries) ? [] : TrackingEntryResource::collection($this->entries),
        ];
    }
}
