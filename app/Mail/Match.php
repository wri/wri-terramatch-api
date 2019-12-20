<?php

namespace App\Mail;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Match extends BaseEmail
{
    public function __construct(Model $user)
    {
        if (!in_array(get_class($user), ["App\\Models\\Admin", "App\\Models\\User"])) {
            throw new Exception();
        }
        $isAdmin = get_class($user) == "App\\Models\\Admin";
        if ($isAdmin) {
            $this->subject = 'Successful Match';
            $this->title = "Successful Match";
            $this->body = "Follow this link to view the match.";
            $this->link = config("app.front_end") . "/admin";
            $this->cta = "View Match";
        } else {
            $this->subject = 'Someone Has Matched With You';
            $this->title = "Someone Has Matched With You";
            $this->body = "Follow this link to view their contact details.";
            $this->link = config("app.front_end") . "/connections";
            $this->cta = "View Contact Details";
        }
    }
}
