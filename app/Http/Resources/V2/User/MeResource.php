<?php

namespace App\Http\Resources\V2\User;

use App\Http\Resources\V2\Organisation\MyOrganisationLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email_address_verified_at' => $this->email_address_verified_at,
            'email_address' => $this->email_address,
            'role' => $this->primary_role ? $this->primary_role->name : '',
            'organisation' => new MyOrganisationLiteResource($this->my_primary_organisation),
        ];
    }
}
