<?php

namespace App\Jobs\Dashboard;

use App\Models\DelayedJob;
use App\Services\Dashboard\RunActiveCountriesTableService;
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

class RunActiveCountriesTableJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    protected $delayed_job_id;

    protected $frameworks;

    protected $landscapes;

    protected $organisations;

    protected $country;

    protected $uuid;

    protected $cacheParameter;

    public function __construct(
        string $delayed_job_id,
        array $frameworks,
        array $landscapes,
        array $organisations,
        string $country,
        string $uuid,
        string $cacheParameter
    ) {
        $this->delayed_job_id = $delayed_job_id;
        $this->frameworks = $frameworks;
        $this->landscapes = $landscapes;
        $this->organisations = $organisations;
        $this->country = $country;
        $this->uuid = $uuid;
        $this->cacheParameter = $cacheParameter;
    }

    public function handle(RunActiveCountriesTableService $runActiveCountriesTableService)
    {
        try {
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);

            $request = new Request([
                'filter' => [
                    'country' => $this->country,
                    'programmes' => $this->frameworks,
                    'landscapes' => $this->landscapes,
                    'organisationType' => $this->organisations,
                    'projectUuid' => $this->uuid,
                ],
            ]);

            $response = $runActiveCountriesTableService->getAllCountries($request);

            Redis::set('dashboard:active-countries-table|' . $this->cacheParameter, json_encode([
                'data' => $response,
            ]));

            $delayedJob->update([
                'status' => DelayedJob::STATUS_SUCCEEDED,
                'payload' => json_encode($response),
                'status_code' => Response::HTTP_OK,
            ]);

        } catch (Exception $e) {
            Log::error('Error in RunActiveCountriesTableJob: ' . $e->getMessage());

            DelayedJob::where('id', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }
}
