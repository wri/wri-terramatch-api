<?php

namespace App\Mail;

use Exception;

class InterestShown extends Mail
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
        $this->subject = 'Someone Has Shown Interest In One Of Your Projects';
        $this->title = 'Someone Has Shown Interest In One Of Your Projects';
        $this->body =
            e($name) . ' has shown interest in one of your projects.<br><br>' .
            'Follow this link to view their project.';
        $this->link = $link;
        $this->cta = 'View Project';
    }
}
