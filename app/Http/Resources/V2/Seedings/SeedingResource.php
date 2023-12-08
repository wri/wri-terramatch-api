<?php

namespace App\Http\Resources\V2\Seedings;

use Illuminate\Http\Resources\Json\JsonResource;

class SeedingResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'weight_of_sample' => $this->weight_of_sample,
            'seeds_in_sample' => $this->seeds_in_sample,
            'amount' => $this->amount,
        ];
    }
}
