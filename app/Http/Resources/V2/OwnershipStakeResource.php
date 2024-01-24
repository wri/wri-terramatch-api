<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class OwnershipStakeResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'title' => $this->title,
            'gender' => $this->gender,
            'percent_ownership' => $this->percent_ownership,
            'year_of_birth' => $this->year_of_birth,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
