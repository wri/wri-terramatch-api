<?php

namespace App\Mail;

use App\Models\PasswordReset as PasswordResetModel;
use Illuminate\Support\Str;

class V2ProjectInviteReceived extends I18nMail
{
    public function __construct(String $name, String $nameOrganisation, String $callbackUrl, $user)
    {
        parent::__construct($user);
        $tokenToCompleteCreateUser = new PasswordResetModel();
        $tokenToCompleteCreateUser->user_id = $user->id;
        $tokenToCompleteCreateUser->token = Str::random(32);
        $tokenToCompleteCreateUser->saveOrFail();
        $this->setSubjectKey('v2-project-invite-received-create.subject')
            ->setTitleKey('v2-project-invite-received-create.title')
            ->setBodyKey('v2-project-invite-received-create.body')
            ->setCta('v2-project-invite-received-create.cta')
            ->setParams([
                '{projectName}' => e($name),
                '{organisationName}' => $nameOrganisation,
            ]);
        $this->link = $callbackUrl . '/' . $tokenToCompleteCreateUser->token;
        $this->transactional = true;
        $this->invite = true;
    }
}
