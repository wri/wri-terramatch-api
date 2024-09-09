<?php

namespace App\Mail;

use App\Models\V2\Organisation;
use Illuminate\Support\Facades\Auth;

class OrganisationUserRejected extends I18nMail
{
    public function __construct(Organisation $organisation)
    {
        $user = Auth::user();
        $this->setSubjectKey('organisation-user-rejected.subject')
            ->setTitleKey('organisation-user-rejected.title')
            ->setBodyKey('organisation-user-rejected.body')
            ->setParams(['{organisationName}' => $organisation->name])
            ->setUserLocale($user->locale);
    }
}
