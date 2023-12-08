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
            'readable_status' => $this->readable_status,
            'type' => $this->type,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
