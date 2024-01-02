<?php

namespace App\Mail;

class V2ProjectMonitoringNotification extends Mail
{
    public function __construct(String $name, $callbackUrl)
    {
        $this->subject = 'You have been added as a monitoring partner.';
        $this->title = 'You have been added as a monitoring partner.';
        $this->body =
            'You have been added to'. e($name) .'as a monitoring partner on TerraMatch. Login into your account
            today to see the project progress and relevant reports.<br><br>
            Login <a href="'.$callbackUrl.'" style="color: #6E6E6E;">Here.</a><br><br>';
        $this->transactional = false;
        $this->monitoring = true;
    }
}
