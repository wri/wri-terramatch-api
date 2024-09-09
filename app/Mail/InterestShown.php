<?php

namespace App\Mail;

use Exception;
use Illuminate\Support\Facades\Auth;

class InterestShown extends I18nMail
{
    public function __construct(String $model, String $name, Int $id)
    {
        switch ($model) {
            case 'Offer':
                $link = '/funding/' . $id;

                break;
            case 'Pitch':
                $link = '/projects/' . $id;

                break;
            default:
                throw new Exception();
        }
        $user = Auth::user();
        $this->setSubjectKey('interest-shown.subject')
            ->setTitleKey('interest-shown.title')
            ->setBodyKey('interest-shown.body')
            ->setCta('interest-shown.cta')
            ->setParams(['{name}' => e($name)])
            ->setUserLocale($user->locale);

        $this->link = $link;
    }
}
