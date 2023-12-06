<?php

namespace App\Jobs;

use App\Mail\UserVerification as UserVerificationMail;
use App\Models\Verification as VerificationModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserVerificationJob implements ShouldQueue
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
        $verification = new VerificationModel();
        $verification->user_id = $this->model->id;
        $verification->token = Str::random(32);
        $verification->saveOrFail();
        Mail::to($this->model->email_address)->send(new UserVerificationMail($verification->token, $this->callbackUrl));
    }
}
