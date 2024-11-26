<?php

namespace App\Mail;

class SendLoginDetails extends I18nMail
{
    public function __construct(String $token, string $callbackUrl = null, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('send-login-details.subject')
            ->setTitleKey('send-login-details.title')
            ->setBodyKey('send-login-details.body')
            ->setParams([
                '{userName}' => e($user->first_name . ' ' . $user->last_name),
                '{mail}' => e($user->email_address),
            ])
            ->setCta('send-login-details.cta');

        $this->link = $callbackUrl ?
            $callbackUrl . urlencode($token) :
            '/set-password?token=' . urlencode($token);

        $this->transactional = true;
    }
}
