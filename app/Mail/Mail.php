<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class Mail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $banner = '';

    public $title = '';

    public $body = '';

    public $link = '';

    public $cta = '';

    public $monitoring = false;

    public $invite = false;

    public function build()
    {
        return $this->view('Emails.master', [
            'frontend_url' => config('app.front_end'),
            'backend_url' => config('app.url'),
            'banner' => $this->banner,
            'title' => $this->title,
            'body' => $this->body,
            'link' => $this->link,
            'cta' => $this->cta,
            'monitoring' => $this->monitoring,
            'invite' => $this->invite,
            'year' => date('Y'),
        ]);
    }

    protected function buildRecipients($message): Mail
    {
        $overrideRecipients = collect(explode(',', getenv('EMAIL_RECIPIENTS')));
        if ($overrideRecipients->isEmpty() || empty($overrideRecipients->first())) {
            return parent::buildRecipients($message);
        }

        $originalRecipients = [];
        foreach (['to', 'cc', 'bcc'] as $type) {
            $originalRecipients[$type] = $this->{$type};
        }
        $message->getHeaders()->addTextHeader('X-Original-Emails', json_encode($originalRecipients));

        $overrideRecipients->each(function ($email) use ($message) { $message->to($email); });

        return $this;
    }
}
