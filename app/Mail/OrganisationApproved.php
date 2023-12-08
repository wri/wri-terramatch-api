<?php

namespace App\Mail;

class OrganisationApproved extends Mail
{
    public function __construct()
    {
        $this->subject = 'Your organization has been accepted into TerraMatch.';
        $this->title = 'YOUR ORGANIZATION HAS BEEN ACCEPTED INTO TERRAMATCH.';
        $this->body = 'Please login to submit an application or report on a monitored project. If you have any questions, please reach out to info@terramatch.org';
        $this->cta = 'LOGIN';
        $this->link = '/auth/login';
    }
}
