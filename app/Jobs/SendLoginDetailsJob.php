<?php

namespace App\Jobs;

use App\Mail\SendLoginDetails;
use App\Models\PasswordReset as PasswordResetModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendLoginDetailsJob implements ShouldQueue
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
        try {
            if (get_class($this->model) !== \App\Models\V2\User::class) {
                throw new Exception('Invalid model type');
            }

            $passwordReset = new PasswordResetModel();
            $passwordReset->user_id = $this->model->id;
            $passwordReset->token = Str::random(32);
            $passwordReset->saveOrFail();
            Mail::to($this->model->email_address)
                ->send(new SendLoginDetails($passwordReset->token, $this->callbackUrl, $this->model));
        } catch (\Throwable $e) {
            Log::error('Job failed', ['error' => $e->getMessage()]);

            throw $e;
        }
    }
}
