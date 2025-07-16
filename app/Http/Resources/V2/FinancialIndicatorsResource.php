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
        $data = [
            'uuid' => $this->uuid,
            'organisation_id' => $this->organisation_id,
            'financial_report_id' => $this->financial_report_id,
            'collection' => $this->collection,
            'amount' => $this->amount,
            'year' => $this->year,
            'description' => $this->description,
            'exchange_rate' => $this->exchange_rate,
        ];

        return $this->appendFilesToResource($data);
    }
}
