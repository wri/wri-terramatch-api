<?php

namespace App\Jobs\V2;

use App\Exports\V2\EntityExport;
use App\Models\DelayedJob;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class GenerateProjectAdminEntityExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    protected $delayed_job_id;

    private string $uuid;

    private string $entity;

    private string $framework;

    public function __construct(string $delayed_job_id, string $uuid, string $entity, string $framework)
    {
        $this->delayed_job_id = $delayed_job_id;
        $this->uuid = $uuid;
        $this->entity = $entity;
        $this->framework = $framework;
    }

    public function handle()
    {
        try {
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);
            $name = 'exports/all-entity-records/'. $this->uuid. $this->entity.'-'.$this->framework.'.csv';
            $modelClass = $this->getModelClass($this->entity);
            $form = $this->getForm($modelClass, $this->framework);

            if (is_null($form)) {
                Log::info("There was no Form for entity $this->entity and framework $this->framework");

                return;
            }

            $user = User::isUuid($this->uuid)->first();

            $ids = $user->frameworkProjects($this->framework)->pluck('id');

            Log::info('ids for: '. $ids);

            $query = $modelClass::where('framework_key', $this->framework);
            if ($this->entity === 'projects') {
                $query->whereIn('id', $ids);
            } else {
                $query->whereIn('project_id', $ids);
            }

            $export = (new EntityExport($query, $form));

            Excel::store($export, $name, 's3');

            Redis::set('export:project-manager|'.$this->uuid.'|'.$this->entity.'|'.$this->framework, $name, 'EX', 86400);

            Log::info("GenerateAdminAllEntityRecordsExportJob for $this->entity and $this->framework ended");

            $delayedJob->update([
                'status' => DelayedJob::STATUS_SUCCEEDED,
                'payload' => json_encode(['file' => $name]),
                'status_code' => Response::HTTP_OK,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DelayedJob::where('id', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }

    private function getForm(string $modelClass, string $framework)
    {
        return Form::where('model', $modelClass)
            ->where('framework_key', $framework)
            ->first();
    }

    private function getModelClass(string $entity)
    {
        switch ($entity) {
            case 'projects':
                return Project::class;
            case 'sites':
                return Site::class;
            case 'nurseries':
                return Nursery::class;
            case 'project-reports':
                return ProjectReport::class;
            case 'site-reports':
                return SiteReport::class;
            case 'nursery-reports':
                return NurseryReport::class;
        }

        throw new \InvalidArgumentException('The entity '. $entity.' is not a valid one');
    }
}
