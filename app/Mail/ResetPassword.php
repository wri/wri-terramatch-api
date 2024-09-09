<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class ResetPassword extends I18nMail
{
    public function __construct(String $token, string $callbackUrl = null)
    {
        $user = Auth::user();
        $this->setSubjectKey('reset-password.subject')
            ->setTitleKey('reset-password.title')
            ->setBodyKey('reset-password.body')
            ->setCta('reset-password.cta')
            ->setUserLocale($user->locale);
        $this->link = $callbackUrl ?
            $callbackUrl . urlencode($token) :
            '/passwordReset?token=' . urlencode($token);
        $this->transactional = true;
    }
}
