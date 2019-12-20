<?php

namespace App\Mail;

use App\Models\Verification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Exception;

class UserVerification extends BaseEmail
{
    public function __construct(Model $user)
    {
        if (!in_array(get_class($user), ["App\\Models\\Admin", "App\\Models\\User"])) {
            throw new Exception();
        }
        $verification = new Verification();
        $verification->user_id = $user->id;
        $verification->token = Str::random(32);
        $verification->saveOrFail();
        $link = "/verify?token=" . urlencode($verification->token);
        $this->subject = 'Verify Your Email Address';
        $this->title = "Verify Your Email Address";
        $this->body =  "Follow this link to verify your email address.";
        $this->link = config("app.front_end") . $link;
        $this->cta = "Verify Email Address";
    }
}
