<?php

namespace App\Http\Resources\V2\RestorationPartners;

use App\Http\Resources\V2\Demographics\DemographicResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RestorationPartnerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'collection' => $this->collection,
            'readable_collection' => $this->readable_collection,
            'demographics' => empty($this->demographics) ? [] : DemographicResource::collection($this->demographics),
        ];
    }
}
