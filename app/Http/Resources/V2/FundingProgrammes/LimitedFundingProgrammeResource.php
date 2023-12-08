<?php

namespace App\Http\Resources\V2\FundingProgrammes;

use Illuminate\Http\Resources\Json\JsonResource;

class LimitedFundingProgrammeResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->translated_name,
            'description' => $this->translated_description,
            'status' => $this->status,
        ];
    }
}
