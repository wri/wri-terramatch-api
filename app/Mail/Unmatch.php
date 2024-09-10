<?php

namespace App\Mail;

use Exception;

class Unmatch extends I18nMail
{
    public function __construct(String $model, String $firstName = '', String $secondName = '', $user)
    {
        parent::__construct($user);
        switch ($model) {
            case 'Admin':
                $isAdmin = true;

                break;
            case 'User':
                $isAdmin = false;

                break;
            default:
                throw new Exception();
        }
        if ($isAdmin) {
            $this->setSubjectKey('unmatch.subject-admin')
                ->setTitleKey('unmatch.title-admin')
                ->setBodyKey('unmatch.body-admin')
                ->setParams(['{firstName}' => e($firstName), '{secondName}' => e($secondName)]);
        } else {
            $this->setSubjectKey('unmatch.subject-user')
                ->setTitleKey('unmatch.title-user')
                ->setBodyKey('unmatch.body-user')
                ->setParams(['{firstName}' => e($firstName)]);
        }
    }
}
