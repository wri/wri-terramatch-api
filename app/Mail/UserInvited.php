<?php

namespace App\Mail;

use Exception;

class UserInvited extends I18nMail
{
    public function __construct(String $emailAddress, String $type, String $callbackUrl = null)
    {
        parent::__construct(null);
        switch ($type) {
            case 'Admin':
                $this->setBodyKey('user-invited.body-admin');

                break;
            case 'User':
                $this->setBodyKey('user-invited.body-user');

                break;
            default:
                throw new Exception();
        }
        $this->setSubjectKey('user-invited.subject')
            ->setTitleKey('user-invited.title')
            ->setCta('user-invited.cta');

        $this->link = $callbackUrl ?
            $callbackUrl . 'invite?emailAddress=' . urlencode($emailAddress) . '&type=' . urlencode(strtolower($type)) :
            '/invite?emailAddress=' . urlencode($emailAddress) . '&type=' . urlencode(strtolower($type));
        $this->transactional = true;
    }
}
