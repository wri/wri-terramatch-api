<?php

namespace App\Mail;

use Exception;

class UpdateVisibility extends Mail
{
    public function __construct(String $model, Int $id)
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
        $this->subject = "Update Your Project's Funding Status";
        $this->title = "Update Your Project's Funding Status";
        $this->body =
            "It's been three days since someone matched with one of your projects. " .
            'Do you need to update its funding status?';
        $this->link = $link;
        $this->cta = 'Update Funding Status';
    }
}
