<?php

namespace App\Http\Resources\V2\Demographics;

use Illuminate\Http\Resources\Json\JsonResource;

class DemographicResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'type' => $this->type,
            'subtype' => $this->subtype,
            'name' => $this->name,
            'amount' => $this->amount,
        ];
    }
}
