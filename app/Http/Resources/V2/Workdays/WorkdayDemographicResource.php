<?php

namespace App\Http\Resources\V2\Workdays;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkdayDemographicResource extends JsonResource
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
