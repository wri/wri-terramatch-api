<?php

namespace App\Mail;

use Illuminate\Support\Str;

class BulkUserCreation extends I18nMail
{
    public function __construct(string $token, string $fundingProgrammeName, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('bulk-user-creation.subject')
            ->setTitleKey('bulk-user-creation.title')
            ->setBodyKey('bulk-user-creation.body')
            ->setParams([
                '{userName}' => e($user->first_name . ' ' . $user->last_name),
                '{mail}' => e($user->email_address),
                '{fundingProgrammeName}' => e($fundingProgrammeName),
            ])
            ->setCta('bulk-user-creation.cta');

        $frontEndUrl = config('app.front_end');
        if (! Str::endsWith($frontEndUrl, '/')) {
            $frontEndUrl .= '/';
        }
        $this->link = $frontEndUrl . 'auth/set-password/' . urlencode($token);

        $this->transactional = true;
    }
}
