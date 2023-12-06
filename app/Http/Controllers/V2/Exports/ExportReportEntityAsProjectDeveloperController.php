<?php

namespace App\Http\Controllers\V2\Exports;

use App\Exports\V2\EntityExport;
use App\Http\Controllers\Controller;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;

class ExportReportEntityAsProjectDeveloperController extends Controller
{
    public function __invoke(Request $request, string $entity, string $uuid)
    {
        ini_set('memory_limit', '-1');
        $modelClass = $this->getModelClass($entity);

        Validator::make(['entity' => $entity, 'uuid' => $uuid], [
            'entity' => 'required|in:site-reports,nursery-reports,project-reports',
            'uuid' => 'required|exists:'.$modelClass.',uuid|max:255',
        ])->validate();

        $model = $modelClass::where('uuid', $uuid)->firstOrFail();
        $project = $model->project;
        $form = $this->getForm($modelClass, $project->framework_key);

        $this->authorize('export', [$modelClass, $form, $project]);

        $zipFilename = public_path('storage/'.Str::of($project->name)->replace(['/', '\\'], '-') . ' - '.Str::of($entity)->replace('-', ' ').' - ' . now() . '.zip');
        $filename = Str::of($project->name)->replace(['/', '\\'], '-') . ' - '.Str::of($entity)->replace('-', ' ').' - ' . now() . '.csv';

        $zip = new \ZipArchive();
        $zip->open($zipFilename, \ZipArchive::CREATE);
        $zip
            ->addFromString(
                $filename,
                (new EntityExport($modelClass::where('id', $model->id), $form))->raw(Excel::CSV)
            );
        $zip->close();

        return response()->download($zipFilename)->deleteFileAfterSend();
    }

    private function getForm(string $modelClass, string $framework)
    {
        return Form::where('model', $modelClass)
            ->where('framework_key', $framework)
            ->firstOrFail();
    }

    private function getModelClass(string $entity)
    {
        $model = null;

        switch ($entity) {
            case 'project-reports':
                $model = ProjectReport::class;

                break;
            case 'site-reports':
                $model = SiteReport::class;

                break;
            case 'nursery-reports':
                $model = NurseryReport::class;

                break;
        }

        return $model;
    }
}
