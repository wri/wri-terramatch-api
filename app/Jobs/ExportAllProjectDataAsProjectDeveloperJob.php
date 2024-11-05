<?php

namespace App\Jobs;

use App\Models\DelayedJob;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Services\ExportAllProjectDataAsProjectDeveloperService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ExportAllProjectDataAsProjectDeveloperJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    protected $uuid;

    protected $delayed_job_id;

    protected $form_uuid;

    protected $project_id;

    public function __construct(string $delayed_job_id, string $form_uuid, string $project_id)
    {
        $this->delayed_job_id = $delayed_job_id;
        $this->form_uuid = $form_uuid;
        $this->project_id = $project_id;
    }

    public function handle(ExportAllProjectDataAsProjectDeveloperService $exportAllProjectDataAsProjectDeveloperService)
    {
        try {
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);
            $project = Project::findOrFail($this->project_id);
            $form = Form::isUuid($this->form_uuid)->firstOrFail();

            $binary_data = $exportAllProjectDataAsProjectDeveloperService->run($form, $project);
            Redis::set('exports:project:'.$project->id, $binary_data, 'EX', 7200);

            $delayedJob->update([
                'status' => DelayedJob::STATUS_SUCCEEDED,
                'payload' => ['message' => 'Total Header Calculation completed'],
                'status_code' => Response::HTTP_OK,
            ]);

        } catch (Exception $e) {
            Log::error('Error in ExportAllProjectDataAsProjectDeveloperJob: ' . $e->getMessage());

            DelayedJob::where('id', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }
}
