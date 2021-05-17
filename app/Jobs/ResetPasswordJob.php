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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function handle()
    {
        if (!in_array(get_class($this->model), ["App\\Models\\Admin", "App\\Models\\User"])) {
            throw new Exception();
        }
        $passwordReset =  new PasswordResetModel();
        $passwordReset->user_id = $this->model->id;
        $passwordReset->token = Str::random(32);
        $passwordReset->saveOrFail();
        Mail::to($this->model->email_address)->send(new ResetPasswordMail($passwordReset->token));
    }
}
