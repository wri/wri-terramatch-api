<?php

namespace App\Mail;

use Exception;
use Illuminate\Support\Facades\Auth;

class MatchMail extends I18nMail
{
    public function __construct(String $model, String $firstName = "", String $secondName = "")
    {
        switch ($model) {
            case "Admin":
                $isAdmin = true;
                break;
            case "Funder":
            case "Developer":
                $isAdmin = false;
                $isFunder = $model == "Funder";
                break;
            default:
                throw new Exception();
        }
        $user = Auth::user();
        if ($isAdmin) {
            $this->setSubjectKey('match-mail.subject-admin')
                ->setTitleKey('match-mail.title-admin')
                ->setBodyKey('match-mail.body-admin')
                ->setCta('match-mail.cta-admin')
                ->setParams([
                    '{firstName}' => e($firstName),
                    '{secondName}' => e($secondName)
                ])
                ->setUserLocale($user->locale);
            $this->link = "/admin/matches";
        } else {
            if ($isFunder) {
                $this->setSubjectKey('match-mail.subject-funder')
                    ->setTitleKey('match-mail.title-funder')
                    ->setBodyKey('match-mail.body-funder')
                    ->setParams(['{firstName}' => e($firstName)])
                    ->setUserLocale($user->locale);
            } else {
                $this->setSubjectKey('match-mail.subject-user')
                    ->setTitleKey('match-mail.title-user')
                    ->setBodyKey('match-mail.body-user')
                    ->setParams(['{firstName}' => e($firstName)])
                    ->setUserLocale($user->locale);
            }
            $this->link = "/connections";
            $this->setCta('match-mail.cta');
        }
    }
}
