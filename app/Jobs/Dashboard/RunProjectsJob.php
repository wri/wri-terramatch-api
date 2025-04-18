<?php

namespace App\Jobs\Dashboard;

use App\Models\DelayedJob;
use App\Services\Dashboard\RunProjectsService;
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

class RunProjectsJob implements ShouldQueue
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

    protected $cohort;

    protected $cacheParameter;

    public function __construct(string $delayed_job_id, array $frameworks, array $landscapes, array $organisations, string $country, string $cohort, string $uuid, string $cacheParameter, ?int $userId = null)
    {
        $this->delayed_job_id = $delayed_job_id;
        $this->frameworks = $frameworks;
        $this->landscapes = $landscapes;
        $this->organisations = $organisations;
        $this->country = $country;
        $this->cohort = $cohort;
        $this->uuid = $uuid;
        $this->cacheParameter = $cacheParameter;
    }

    public function handle(RunProjectsService $runProjectsService)
    {
        try {
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);

            $request = new Request(
                [
                    'filter' => [
                        'country' => $this->country,
                        'programmes' => $this->frameworks,
                        'landscapes' => $this->landscapes,
                        'organisationType' => $this->organisations,
                        'projectUuid' => $this->uuid,
                        'cohort' => $this->cohort,
                        'has_polygons' => true,
                    ],
                ]
            );
            $response = $runProjectsService->runProjectsJob($request);
            Redis::set('dashboard:projects|' . $this->cacheParameter, json_encode($response));

            $delayedJob->update([
                'status' => DelayedJob::STATUS_SUCCEEDED,
                'payload' => json_encode($response),
                'status_code' => Response::HTTP_OK,
            ]);

        } catch (Exception $e) {
            Log::error('Error in RunProjectsJob: ' . $e->getMessage());

            DelayedJob::where('id', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }
}
