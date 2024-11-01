<?php

namespace App\Jobs\Dashboard;

use App\Models\DelayedJob;
use App\Services\Dashboard\RunTotalHeaderService;
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

class RunTotalHeaderJob implements ShouldQueue
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

    public function __construct(string $delayed_job_id, array $frameworks, array $landscapes, array $organisations, string $country, int $cacheParameter)
    {
        $this->delayed_job_id = $delayed_job_id;
        $this->frameworks = $frameworks;
        $this->landscapes = $landscapes;
        $this->organisations = $organisations;
        $this->country = $country;
        $this->cacheParameter = $cacheParameter;
    }

    public function handle(RunTotalHeaderService $runTotalHeaderService)
    {
        try {
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);

            $request = new Request(
        [
                    'filter.country' => $this->country,
                    'filter.programmes' => $this->frameworks,
                    'filter.landscapes' => $this->landscapes,
                    'filter.organisations.type' => $this->organisations,
                ]
            );
            $response = $runTotalHeaderService->runTotalHeaderJob($request);
            Redis::set('total-section-header-' . $this->cacheParameter, json_encode($response));


            $delayedJob->update([
                'status' => DelayedJob::STATUS_SUCCEEDED,
                'payload' => ['message' => 'Total Header Calculation completed'],
                'status_code' => Response::HTTP_OK,
            ]);

        } catch (Exception $e) {
            Log::error('Error in RunTotalHeaderJob: ' . $e->getMessage());

            DelayedJob::where('id', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }
}
