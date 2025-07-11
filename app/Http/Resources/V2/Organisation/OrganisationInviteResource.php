<?php

namespace App\Http\Resources\V2\Organisation;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationInviteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'organisation_id' => $this->organisation->id,
            'email_address' => $this->email_address,
            'accepted_at' => $this->accepted_at,
            'created_at' => $this->created_at,
        ];
    }
}
