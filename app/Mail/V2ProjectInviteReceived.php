<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;


class V2ProjectInviteReceived extends I18nMail
{
    public function __construct(String $name, String $nameOrganisation, String $callbackUrl)
    {
        $user = Auth::user();
        // $this->subject = 'You have been invited to join TerraMatch';
        // $this->title = 'You have been invited to join TerraMatch';
        // $this->body =
        //     $nameOrganisation .' has invited you to join TerraMatch as a monitoring
        //     partner to '. e($name) .'. Set an account password today to see the project’s
        //     progress and access their latest reports.<br><br>
        //     Reset your password <a href="'.$callbackUrl.'" style="color: #6E6E6E;">Here.</a><br><br>';
        $this->link = $callbackUrl ?
        $callbackUrl . '' :
        '';
        $this->transactional = true;
        $this->invite = true;

        $this->setSubjectKey('project-invite-received.subject')
            ->setTitleKey('project-invite-received.title')
            ->setBodyKey('project-invite-received.body')
            ->setUserLocale($user->locale);
    }
}
