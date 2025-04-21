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
        $documentation = $this->getMedia('documentation')->first();

        return [
            'uuid' => $this->uuid,
            'organisation_id' => $this->organisation_id,
            'collection' => $this->collection,
            'amount' => $this->amount,
            'year' => $this->year,
            'documentation' => empty($documentation) ? null : array_merge(
                $documentation->toArray(),
                ['full_url' => $documentation->getFullUrl()]
            ),
            'description' => $this->description,
        ];
    }
}
