<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class FinancialIndicatorsResource extends JsonResource
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
            'collection' => $this->collection,
            'amount' => $this->amount,
            'year' => $this->year,
            'documentation' => $this->documentation,
            'description' => $this->description,
        ];
    }
}
