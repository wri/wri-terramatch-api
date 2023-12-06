<?php

namespace App\Http\Resources\V2\User;

use Illuminate\Http\Resources\Json\JsonResource;

class LimitedUserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'user_type' => $this->role,
            'job_role' => $this->job_role,

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email_address' => $this->email_address,
            'phone_number' => $this->phone_number,
            'whatsapp_phone' => $this->whatsapp_phone,
        ];
    }
}
