<?php

namespace App\Http\Controllers\V2\Exports;

use App\Http\Controllers\Controller;
use App\Models\Framework;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Writer;

class ExportAllMonitoredEntitiesController extends Controller
{
    public function __invoke(Request $request, string $entity, string $framework)
    {
        $modelClass = $this->getModelClass($entity);
        $framework = $this->getSlug($framework);
        $form = $this->getForm($modelClass, $framework);
        $this->authorize('export', [$modelClass, $form]);

        $url = Storage::disk('s3')->temporaryUrl('exports/all-entity-records/'.$entity.'-'.$framework.'.csv', now()->addMinutes(5));

        $csv = Writer::createFromString(file_get_contents($url));

        $filename = Str::of(data_get($form, 'title', 'Form'))->replace(['/', '\\'], '-') . ' '.Str::of($entity)->replace('-', ' ')->ucfirst().' - ' . now() . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function getSlug(string $framework)
    {
        $frameworkModel = Framework::where('access_code', $framework)->firstOrFail();
        return $frameworkModel->slug;
    }
    private function getForm(string $modelClass, string $framework)
    {
        return Form::where('model', $modelClass)
            ->where('framework_key', $framework)
            ->firstOrFail();
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
