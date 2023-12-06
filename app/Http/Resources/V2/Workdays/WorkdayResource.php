<?php

namespace App\Http\Resources\V2\Workdays;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkdayResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'collection' => $this->collection,
            'readable_collection' => $this->readable_collection,
            'amount' => $this->amount,
            'gender' => $this->gender,
            'age' => $this->age,
            'ethnicity' => $this->ethnicity,
        ];
    }
}
