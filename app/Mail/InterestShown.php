<?php

namespace App\Mail;

class InterestShown extends BaseEmail
{
    public function __construct()
    {
        $this->subject = 'Someone Has Shown Interest In You';
        $this->title = "Someone Has Shown Interest In You";
        $this->body =  "Follow this link to view their project.";
        $this->link = config("app.front_end") . "/connections";
        $this->cta = "View Project";
    }
}
