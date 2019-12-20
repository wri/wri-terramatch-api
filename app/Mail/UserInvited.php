<?php

namespace App\Mail;

use Illuminate\Database\Eloquent\Model;
use Exception;

class UserInvited extends BaseEmail
{
    public function __construct(Model $user)
    {
        if (!in_array(get_class($user), ["App\\Models\\Admin", "App\\Models\\User"])) {
            throw new Exception();
        }
        $link = "/invite?emailAddress=" . urlencode($user->email_address) . "&type=" . urlencode($user->role);
        $this->subject = 'Create Your Account';
        $this->title = 'Create Your Account';
        $this->body = "Follow this link to create your account.";
        $this->link =  config("app.front_end") . $link;
        $this->cta = "Create Account";
    }
}
