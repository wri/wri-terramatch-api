<?php

namespace App\Http\Controllers\V2\Exports;

use App\Exports\V2\EntityExport;
use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;

class ExportProjectEntityAsProjectDeveloperController extends Controller
{
    public function __invoke(Request $request, Project $project, string $entity)
    {
        ini_set('memory_limit', '-1');
        Validator::make(['entity' => $entity], [
            'entity' => 'required|in:sites,nurseries,project-reports,shapefiles',
        ])->validate();

        if ($entity === 'shapefiles') {
            return $this->exportShapefiles($project);
        }

        $modelClass = $this->getModelClass($entity);

        $form = $this->getForm($modelClass, $project->framework_key);
        $this->authorize('export', [$modelClass, $form, $project]);

        $filename = Str::of($project->name)->replace(['/', '\\'], '-') . ' - '.$entity.' establishment data - ' . now() . '.csv';

        $query = $modelClass::where('project_id', $project->id);

        return (new EntityExport($query, $form))->download($filename, Excel::CSV);//->deleteFileAfterSend(true);
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
            case 'sites':
                $model = Site::class;

                break;
            case 'nurseries':
                $model = Nursery::class;

                break;
            case 'project-reports':
                $model = ProjectReport::class;

                break;
        }

        return $model;
    }

    private function exportShapefiles(Project $project)
    {
        $geoJson = GeometryHelper::generateGeoJSON($project);
        $filename = Str::of($project->name)->replace(['/', '\\'], '-') . ' Sites GeoJSON - ' . now() . '.geojson';
        $path = storage_path('./' . $filename);

        file_put_contents($path, json_encode($geoJson, JSON_PRETTY_PRINT));

        return response()->download($path)->deleteFileAfterSend();
    }

    private function addSiteShapefiles(Project $project, \ZipArchive $mainZip): void
    {
        $shapefilesFolder = 'Sites Shapefiles/';
        $mainZip->addEmptyDir($shapefilesFolder);

        foreach ($project->sites as $site) {
            $geojsonFilename = $shapefilesFolder . Str::of($site->name)->replace(['/', '\\'], '-') . '.geojson';
            $mainZip->addFromString($geojsonFilename, $site->boundary_geojson);
        }
    }
}
