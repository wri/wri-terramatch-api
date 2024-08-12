<?php

namespace App\Http\Resources\V2\User;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLiteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'role' => $this->primary_role->name,
            'job_role' => $this->job_role,

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email_address' => $this->email_address,
            'phone_number' => $this->phone_number,
            'whatsapp_phone' => $this->whatsapp_phone,

            'organisation' => new OrganisationLiteResource($this->my_organisation),

            'last_logged_in_at' => $this->last_logged_in_at,
            'email_address_verified_at' => $this->email_address_verified_at,
            'verified' => ! empty($this->email_address_verified_at),
            'date_added' => $this->created_at,

            'banners' => $this->banners,

            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
