<?php

namespace App\Mail;

class UserVerification extends I18nMail
{
    public function __construct(String $token, string $callbackUrl = null, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('user-verification.subject')
            ->setTitleKey('user-verification.title')
            ->setBodyKey('user-verification.body')
            ->setCta('user-verification.cta');
        $this->link = $callbackUrl ?
            $callbackUrl . urlencode($token) :
            '/verify?token=' . urlencode($token);
        $this->transactional = true;
    }
}
