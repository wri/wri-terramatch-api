<?php

namespace App\Mail;

use App\Models\V2\Organisation;

class OrganisationUserJoinRequested extends Mail
{
    public function __construct(Organisation $organisation)
    {
        $this->subject = 'A user has requested to join your organization';
        $this->title = 'A user has requested to join your organization';
        $this->body = 'A user has requested to join your organization. Please go to the ‘Meet the Team’ page to review this request.';
    }
}
