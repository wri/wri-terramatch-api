<?php

namespace App\Http\Resources\V2\Organisation;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationLiteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'status' => $this->status,
            'is_test' => $this->is_test,
            'readable_status' => $this->readable_status,
            'type' => $this->type,
            'currency' => $this->currency,
            'fin_start_month' => $this->fin_start_month,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
