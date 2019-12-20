<?php

namespace App\Mail;

use App\Models\PasswordReset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Exception;

class ResetPassword extends BaseEmail
{
    public function __construct(Model $user)
    {
        if (!in_array(get_class($user), ["App\\Models\\Admin", "App\\Models\\User"])) {
            throw new Exception();
        }
        $passwordReset =  new PasswordReset();
        $passwordReset->user_id = $user->id;
        $passwordReset->token = Str::random(32);
        $passwordReset->saveOrFail();
        $link = "/passwordReset?token=" . urlencode($passwordReset->token);
        $this->subject = 'Reset Your Password';
        $this->title = "Reset Your Password";
        $this->body = "Follow this link to reset your password.";
        $this->link =  config("app.front_end") . $link;
        $this->cta = "Reset Password";
    }
}
