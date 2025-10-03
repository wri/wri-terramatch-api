<?php

namespace App\Jobs;

use App\Models\DelayedJob;
use App\Models\V2\User;
use App\Services\MyActionsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunIndexMyActionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    protected string $delayed_job_id;

    protected User $user;

    public function __construct(string $delayed_job_id, User $user)
    {
        $this->delayed_job_id = $delayed_job_id;
        $this->user = $user;
    }

    public function handle(MyActionsService $service): void
    {
        try {
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);

            $payload = $service->getPendingActionsPayloadForUser($this->user);

            $delayedJob->update([
                'status' => DelayedJob::STATUS_SUCCEEDED,
                'payload' => ['data' => $payload],
                'status_code' => Response::HTTP_OK,
            ]);
        } catch (Exception $e) {
            Log::error('Error in RunIndexMyActionsJob: ' . $e->getMessage());

            DelayedJob::where('id', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }
}
