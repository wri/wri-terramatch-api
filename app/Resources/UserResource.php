<?php

namespace App\Resources;

use App\Models\User as UserModel;

class UserResource
{
    public function __construct(UserModel $user)
    {
        $this->id = $user->id;
        $this->organisation_id = $user->organisation_id;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email_address = $user->email_address;
        $this->role = $user->role;
        $this->email_address_verified_at = $user->email_address_verified_at;
        $this->last_logged_in_at = $user->last_logged_in_at;
        $this->job_role = $user->job_role;
        $this->twitter = $user->twitter;
        $this->facebook = $user->facebook;
        $this->linkedin = $user->linkedin;
        $this->instagram = $user->instagram;
        $this->avatar = $user->avatar;
        $this->phone_number = $user->phone_number;
    }
}