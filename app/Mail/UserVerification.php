<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class UserVerification extends I18nMail
{
    public function __construct(String $token, string $callbackUrl = null)
    {
        $user = Auth::user();
        $this->setSubjectKey('user-verification.subject')
            ->setTitleKey('user-verification.title')
            ->setBodyKey('user-verification.body')
            ->setCta('user-verification.cta')
            ->setUserLocation($user->locale);
        $this->link = $callbackUrl ?
            $callbackUrl . urlencode($token) :
            '/verify?token=' . urlencode($token);
        $this->transactional = true;
    }
}
