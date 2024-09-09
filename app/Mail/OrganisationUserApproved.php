<?php

namespace App\Mail;

use App\Models\V2\Organisation;
use Illuminate\Support\Facades\Auth;

class OrganisationUserApproved extends I18nMail
{
    public function __construct(Organisation $organisation)
    {
        $user = Auth::user();
        $this->setSubjectKey('organisation-user-approved.subject')
            ->setTitleKey('organisation-user-approved.title')
            ->setBodyKey('organisation-user-approved.body')
            ->setParams(['{organisationName}' => $organisation->name])
            ->setUserLocale($user->locale);
    }
}
