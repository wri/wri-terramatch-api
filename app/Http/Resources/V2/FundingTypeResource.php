<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class FundingTypeResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'organisation_id' => $this->organisation_id,
            'source' => $this->source,
            'amount' => $this->amount,
            'year' => $this->year,
            'type' => $this->type,
        ];
    }
}
