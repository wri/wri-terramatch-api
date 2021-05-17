<?php

namespace App\Mail;

use Exception;

class UserInvited extends Mail
{
    public function __construct(String $emailAddress, String $type)
    {
        switch ($type) {
            case "Admin":
                $prefix = "You've been invited to the administration.";
                break;
            case "User":
                $prefix = "You've been invited to an organisation.";
                break;
            default:
                throw new Exception();
        }
        $this->subject = 'Create Your Account';
        $this->title = 'Create Your Account';
        $this->body =
            $prefix . "<br><br>" .
            "Follow this link to create your account.";
        $this->link = "/invite?emailAddress=" . urlencode($emailAddress) . "&type=" . urlencode(strtolower($type));
        $this->cta = "Create Account";
        $this->transactional = true;
    }
}
