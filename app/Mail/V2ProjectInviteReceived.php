<?php

namespace App\Mail;

class V2ProjectInviteReceived extends Mail
{
    public function __construct(String $name, String $nameOrganisation, String $callbackUrl)
    {
        $this->subject = 'You have been invited to join TerraMatch';
        $this->title = 'You have been invited to join TerraMatch';
        $this->body =
            $nameOrganisation .'has invited you to join TerraMatch as a monitoring
            partner to '. e($name) .'. Create an account today to see the project’s
            progress and access their latest reports.<br><br>
            Create an Account <a href="'.$callbackUrl.'" style="color: #6E6E6E;">Here.</a><br><br>';
        $this->link = $callbackUrl ?
        $callbackUrl . '':
        '';
        $this->transactional = true;
        $this->invite = true;
    }
}
