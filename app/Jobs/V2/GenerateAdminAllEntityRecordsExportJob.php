<?php

namespace App\Jobs\V2;

use App\Exports\V2\EntityExport;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class GenerateAdminAllEntityRecordsExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    private string $entity;

    private string $framework;

    public function __construct(string $entity, string $framework)
    {
        $this->entity = $entity;
        $this->framework = $framework;
    }

    public function handle()
    {
        Log::info("GenerateAdminAllEntityRecordsExportJob for $this->entity and $this->framework started");

        $name = 'exports/all-entity-records/'.$this->entity.'-'.$this->framework.'.csv';
        $modelClass = $this->getModelClass($this->entity);
        $form = $this->getForm($modelClass, $this->framework);

        if (is_null($form)) {
            Log::info("There was no Form for entity $this->entity and framework $this->framework");

            return;
        }


        $export = (new EntityExport($modelClass::where('framework_key', $this->framework), $form));

        Excel::store($export, $name, 's3');

        Log::info("GenerateAdminAllEntityRecordsExportJob for $this->entity and $this->framework ended");
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
