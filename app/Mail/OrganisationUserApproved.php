<?php

namespace App\Mail;

use App\Models\V2\Organisation;

class OrganisationUserApproved extends I18nMail
{
    public function __construct(Organisation $organisation, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('organisation-user-approved.subject')
            ->setTitleKey('organisation-user-approved.title')
            ->setBodyKey('organisation-user-approved.body')
            ->setParams(['{organisationName}' => $organisation->name]);
    }
}
