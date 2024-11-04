<?php

namespace App\Jobs\Dashboard;

use App\Models\DelayedJob;
use App\Services\Dashboard\RunHectaresRestoredService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RunHectaresRestoredJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    protected $uuid;

    protected $delayed_job_id;

    protected $frameworks;

    protected $landscapes;

    protected $organisations;

    protected $country;

    protected $cacheParameter;

    public function __construct(string $delayed_job_id, array $frameworks, array $landscapes, array $organisations, string $country, string $uuid, string $cacheParameter)
    {
        $this->delayed_job_id = $delayed_job_id;
        $this->frameworks = $frameworks;
        $this->landscapes = $landscapes;
        $this->organisations = $organisations;
        $this->country = $country;
        $this->uuid = $uuid;
        $this->cacheParameter = $cacheParameter;
    }

    public function handle(RunHectaresRestoredService $runHectaresRestoredService)
    {
        try {
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);

            $request = new Request(
                [
                    'filter' => [
                        'country' => $this->country,
                        'programmes' => $this->frameworks,
                        'landscapes' => $this->landscapes,
                        'organisationsType' => $this->organisations,
                        'projectUuid' => $this->uuid
                    ],
                ]
            );
            $response = $runHectaresRestoredService->runHectaresRestoredJob($request);
            Redis::set('dashboard:indicator/hectares-restoration' . $this->cacheParameter, json_encode($response));


            $delayedJob->update([
                'status' => DelayedJob::STATUS_SUCCEEDED,
                'payload' => ['message' => 'Hectares Restored Calculation completed'],
                'status_code' => Response::HTTP_OK,
            ]);

        } catch (Exception $e) {
            Log::error('Error in RunHectaresRestoredJob: ' . $e->getMessage());

            DelayedJob::where('id', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }
}
