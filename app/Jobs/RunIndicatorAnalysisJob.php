<?php

namespace App\Jobs;

use App\Models\DelayedJob;
use App\Services\RunIndicatorAnalysisService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RunIndicatorAnalysisJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    protected $delayed_job_id;

    protected $request;

    protected $slug;

    public function __construct(string $delayed_job_id, array $request, string $slug)
    {
        $this->delayed_job_id = $delayed_job_id;
        $this->request = $request;
        $this->slug = $slug;
    }

    public function handle(RunIndicatorAnalysisService $runIndicatorAnalysisService)
    {
        try {
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);

            $binary_data = $runIndicatorAnalysisService->run($this->request, $this->slug);
            Redis::set('run:indicator|'.$this->slug, $binary_data);

            $delayedJob->update([
                'status' => DelayedJob::STATUS_SUCCEEDED,
                'payload' => ['message' => 'Analysis completed'],
                'status_code' => Response::HTTP_OK,
            ]);

        } catch (Exception $e) {
            Log::error('Error in the analysis: ' . $e->getMessage());

            DelayedJob::where('id', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }
}
