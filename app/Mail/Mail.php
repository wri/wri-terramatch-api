<?php

namespace App\Mail;

use App\Models\User as UserModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

abstract class Mail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $banner = "";
    public $title = "";
    public $body = "";
    public $link = "";
    public $cta = "";
    public $transactional = false;

    public function build()
    {
        if (!$this->transactional) {
            $to = $this->to;
            if (count($to) != 1) {
                throw new Exception();
            }
            $emailAddress = $to[0]["address"];
            $user = UserModel::where("email_address", "=", $emailAddress)->firstOrFail();
            $encodedId = Crypt::encryptString(strval($user->id));
            $unsubscribe = "/users/" . urlencode($encodedId) . "/unsubscribe";
        } else {
            $unsubscribe = "";
        }
        return $this->view('Emails.master', [
            "frontend_url" => Config::get("app.front_end"),
            "backend_url" => Config::get("app.url"),
            "banner" => $this->banner,
            "title" => $this->title,
            "body" => $this->body,
            "link" => $this->link,
            "cta" => $this->cta,
            "transactional" => $this->transactional,
            "unsubscribe" => $unsubscribe,
            "year" => date("Y")
        ]);
    }
}
