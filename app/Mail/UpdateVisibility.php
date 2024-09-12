<?php

namespace App\Mail;

use Exception;

class UpdateVisibility extends I18nMail
{
    public function __construct(String $model, Int $id, $user)
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
        $this->setSubjectKey('update-visibility.subject')
            ->setTitleKey('update-visibility.title')
            ->setBodyKey('update-visibility.body')
            ->setCta('update-visibility.cta');
        $this->link = $link;
    }
}
