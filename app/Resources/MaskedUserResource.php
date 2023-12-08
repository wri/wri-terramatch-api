<?php

namespace App\Resources;

use App\Models\User as UserModel;

class MaskedUserResource
{
    public function __construct(UserModel $user)
    {
        $this->id = $user->id;
        $this->uuid = $user->uuid;
        $this->organisation_id = $user->organisation_id;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->job_role = $user->job_role;
        $this->avatar = $user->avatar;
        $this->twitter = $user->twitter;
        $this->facebook = $user->facebook;
        $this->linkedin = $user->linkedin;
        $this->instagram = $user->instagram;
        $this->whatsapp_phone = $user->whatsapp_phone;
    }
}
