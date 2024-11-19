<?php

namespace App\Jobs;

use App\Models\DelayedJob;
use App\Services\ExportAllOrganisationsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class ExportAllOrganisationsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    protected $uuid;

    protected $delayed_job_id;

    protected $file_name;

    public function __construct(string $delayed_job_id, string $file_name)
    {
        $this->file_name = $file_name;
        $this->delayed_job_id = $delayed_job_id;
    }

    public function handle(ExportAllOrganisationsService $exportAllOrganisationsService)
    {
        try {
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);
            $relativePath = 'exports/' . $this->file_name;

            Excel::store($exportAllOrganisationsService->run(), $relativePath);

            $absolutePath = storage_path('app/' . $relativePath);
            $binary_data = file_get_contents($absolutePath);

            Redis::set('exports:organisations:'.$this->file_name, $binary_data, 'EX', 7200);
            $delayedJob->update([
                'status' => DelayedJob::STATUS_SUCCEEDED,
                'payload' => ['message' => 'All Organisations Export completed'],
                'status_code' => Response::HTTP_OK,
            ]);
        } catch (Exception $e) {
            Log::error('Error in ExportAllOrganisationsJob: ' . $e->getMessage());

            DelayedJob::where('id', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }
}
