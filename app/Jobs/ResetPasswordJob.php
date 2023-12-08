<?php

namespace App\Jobs;

use App\Mail\ResetPassword as ResetPasswordMail;
use App\Models\PasswordReset as PasswordResetModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ResetPasswordJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $model;

    private $callbackUrl;

    public function __construct(Model $model, ?string $callbackUrl)
    {
        $this->model = $model;
        $this->callbackUrl = $callbackUrl;
    }

    public function handle()
    {
        if (! in_array(get_class($this->model), [\App\Models\Admin::class, \App\Models\User::class])) {
            throw new Exception();
        }
        $passwordReset = new PasswordResetModel();
        $passwordReset->user_id = $this->model->id;
        $passwordReset->token = Str::random(32);
        $passwordReset->saveOrFail();
        Mail::to($this->model->email_address)->send(new ResetPasswordMail($passwordReset->token, $this->callbackUrl));
    }
}
