<?php

namespace App\Mail;

use Exception;
use Illuminate\Support\Facades\Auth;

class UserInvited extends I18nMail
{
    public function __construct(String $emailAddress, String $type, String $callbackUrl = null)
    {
        $user = Auth::user();
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
            ->setCta('user-invited.cta')
            ->setUserLocation($user->locale);

        $this->link = $callbackUrl ?
            $callbackUrl . 'invite?emailAddress=' . urlencode($emailAddress) . '&type=' . urlencode(strtolower($type)) :
            '/invite?emailAddress=' . urlencode($emailAddress) . '&type=' . urlencode(strtolower($type));
        $this->transactional = true;
    }
}
