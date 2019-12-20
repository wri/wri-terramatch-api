<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class BaseEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $title = "";
    public $body = "";
    public $link = "";
    public $cta = "";

    public function build()
    {
        return $this->view('Emails.master')->with([
            "logo" => config("app.url") . "/images/email_logo.jpg",
            "title" => $this->title,
            "body" => $this->body,
            "link" => $this->link,
            "cta" => $this->cta,
            "year" => date("Y")
        ]);
    }
}
