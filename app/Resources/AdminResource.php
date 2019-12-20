<?php

namespace App\Resources;

use App\Models\Admin as AdminModel;

class AdminResource extends Resource
{
    public $id = null;
    public $first_name = null;
    public $last_name = null;
    public $email_address = null;
    public $role = null;
    public $last_logged_in_at = null;
    public $email_address_verified_at = null;
    public $job_role = null;
    public $avatar = null;

    public function __construct(AdminModel $admin)
    {
        $this->id = $admin->id;
        $this->first_name = $admin->first_name;
        $this->last_name = $admin->last_name;
        $this->email_address = $admin->email_address;
        $this->role = $admin->role;
        $this->email_address_verified_at = $admin->email_address_verified_at;
        $this->last_logged_in_at = $admin->last_logged_in_at;
        $this->job_role = $admin->job_role;
        $this->avatar = $admin->avatar;
    }
}