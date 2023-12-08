<?php

namespace App\Mail;

class OrganisationRejected extends Mail
{
    public function __construct()
    {
        $this->subject = 'Your organization has been rejected from joining TerraMatch.';
        $this->title = 'Your organization has been rejected from joining TerraMatch.';
        $this->body = 'This could be due to the fact that your organization is already on TerraMatch, 
            your organization will not benefit from the services that TerraMatch provides 
            or we do not have enough information to understand what your organization does. 
            Please login to TerraMatch to view a more detail description about why your 
            organization request has been rejected.';
    }
}
