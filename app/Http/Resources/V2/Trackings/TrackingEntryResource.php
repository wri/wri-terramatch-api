<?php

namespace App\Http\Resources\V2\Trackings;

use Illuminate\Http\Resources\Json\JsonResource;

class TrackingEntryResource extends JsonResource
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
