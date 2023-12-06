<?php

namespace App\Mail;

use App\Models\V2\Organisation;

class OrganisationUserApproved extends Mail
{
    public function __construct(Organisation $organisation)
    {
        $this->subject = 'You have been accepted to join ' . $organisation->name . ' on TerraMatch';
        $this->title = 'You have been accepted to join ' . $organisation->name . ' on TerraMatch';
        $this->body = 'You have been accepted to join ' . $organisation->name . ' on TerraMatch. Log-in to view or update your organization’s information.';
    }
}
