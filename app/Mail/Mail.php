<?php

namespace App\Mail;

use App\Models\User as UserModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;

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

    public $transactional = false;

    public function build()
    {
        if (! $this->transactional) {
            $to = $this->to;
            if (count($to) != 1) {
                throw new Exception();
            }
            $emailAddress = $to[0]['address'];
            $user = UserModel::where('email_address', '=', $emailAddress)->firstOrFail();
            $encodedId = Crypt::encryptString(strval($user->id));
            $unsubscribe = '/users/' . urlencode($encodedId) . '/unsubscribe';
        } else {
            $unsubscribe = '';
        }

        return $this->view('Emails.master', [
            'frontend_url' => config('app.front_end'),
            'backend_url' => config('app.url'),
            'banner' => $this->banner,
            'title' => $this->title,
            'body' => $this->body,
            'link' => $this->link,
            'cta' => $this->cta,
            'transactional' => $this->transactional,
            'monitoring' => $this->monitoring,
            'invite' => $this->invite,
            'unsubscribe' => $unsubscribe,
            'year' => date('Y'),
        ]);
    }
}
