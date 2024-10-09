<?php

namespace App\Mail;

use Exception;

class InterestShown extends I18nMail
{
    public function __construct(String $model, String $name, Int $id, $user)
    {
        parent::__construct($user);
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
        $this->setSubjectKey('interest-shown.subject')
            ->setTitleKey('interest-shown.title')
            ->setBodyKey('interest-shown.body')
            ->setCta('interest-shown.cta')
            ->setParams(['{name}' => e($name)]);

        $this->link = $link;
    }
}
