<?php

namespace App\Mail;

use App\Models\V2\Organisation;

class OrganisationUserRejected extends Mail
{
    public function __construct(Organisation $organisation)
    {
        $this->subject = 'Your request to join ' . $organisation->name . ' on TerraMatch has been rejected';
        $this->title = 'Your request to join ' . $organisation->name . ' on TerraMatch has been rejected';
        $this->body = 'Your request to join ' . $organisation->name . ' on TerraMatch has been rejected. <br><br>' .
            'Please set-up a new organizational profile on TerraMatch if you wish to join the platform. ' .
            'Please reach out the help center here if you need more information: <a href="https://terramatchsupport.zendesk.com/hc/en-us/requests/new">https://terramatchsupport.zendesk.com/hc/en-us/requests/new</a>';
    }
}
