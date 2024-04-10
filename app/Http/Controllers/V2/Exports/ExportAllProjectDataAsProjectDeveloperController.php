<?php

namespace App\Http\Controllers\V2\Exports;

use App\Exports\V2\EntityExport;
use App\Http\Controllers\Controller;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;

class ExportAllProjectDataAsProjectDeveloperController extends Controller
{
    public function __invoke(Request $request, Project $project)
    {
        ini_set('memory_limit', '-1');
        $form = $this->getForm(Project::class, $project->framework_key);
        $this->authorize('export', [Project::class, $form, $project]);

        $filename = storage_path('./'.Str::of($project->name)->replace(['/', '\\'], '-') . ' full export - ' . now() . '.zip');
        $zip = new \ZipArchive();
        $zip->open($filename, \ZipArchive::CREATE);

        rescue(function () use ($project, $zip, $form) {
            $this->addProjectExports($project, $zip, $form);
        });
        rescue(function () use ($project, $zip) {
            $this->addProjectReportExports($project, $zip);
        });
        rescue(function () use ($project, $zip) {
            $this->addSitesExports($project, $zip);
        });
        rescue(function () use ($project, $zip) {
            $this->addSiteReportsExports($project, $zip);
        });
        rescue(function () use ($project, $zip) {
            $this->addSiteShapefiles($project, $zip);
        });
        rescue(function () use ($project, $zip) {
            $this->addNurseriesExports($project, $zip);
        });

        rescue(function () use ($project, $zip) {
            $this->addNurseryReportsExports($project, $zip);
        });

        $zip->close();

        return response()->download($filename)->deleteFileAfterSend();
    }

    private function addSiteReportsExports(Project $project, \ZipArchive $mainZip): void
    {
        $form = $this->getForm(SiteReport::class, $project->framework_key);

        foreach ($project->sites as $site) {
            $filename = 'site reports/'.Str::of($project->name . ' - '.$site->name)->replace(['/', '\\'], '-') . ' - site reports.csv';
            $mainZip
                ->addFromString(
                    $filename,
                    (new EntityExport(SiteReport::parentId($site->id), $form))->raw(Excel::CSV)
                );
        }
    }

    private function addSiteShapefiles(Project $project, \ZipArchive $mainZip): void
    {
        $shapefilesFolder = 'Sites Shapefiles/';
        $mainZip->addEmptyDir($shapefilesFolder);

        foreach ($project->sites as $site) {
            $filename = $shapefilesFolder . Str::of($site->name)->replace(['/', '\\'], '-') . '.geojson';
            $mainZip->addFromString($filename, $site->boundary_geojson);
        }
    }

    private function addNurseryReportsExports(Project $project, \ZipArchive $mainZip): void
    {
        $form = $this->getForm(NurseryReport::class, $project->framework_key);

        foreach ($project->nurseries as $nursery) {
            $filename = 'nursery reports/'.Str::of($project->name . ' - '.$nursery->name)->replace(['/', '\\'], '-') . ' - nursery reports.csv';
            $mainZip
                ->addFromString(
                    $filename,
                    (new EntityExport(NurseryReport::parentId($nursery->id), $form))->raw(Excel::CSV)
                );
        }
    }

    private function addProjectExports(Project $project, \ZipArchive $mainZip, Form $form): void
    {
        $filename = Str::of($project->name)->replace(['/', '\\'], '-') . ' - project establishment data.csv';

        $mainZip
            ->addFromString(
                $filename,
                (new EntityExport(Project::query()->where('id', $project->id), $form))->raw(Excel::CSV)
            );
    }

    private function addProjectReportExports(Project $project, \ZipArchive $mainZip): void
    {
        $form = $this->getForm(ProjectReport::class, $project->framework_key);
        $filename = Str::of($project->name)->replace(['/', '\\'], '-') . ' - project reports.csv';

        $mainZip
            ->addFromString(
                $filename,
                (new EntityExport($project->reports()->getQuery(), $form))->raw(Excel::CSV)
            );
    }

    private function addSitesExports(Project $project, \ZipArchive $mainZip): void
    {
        $form = $this->getForm(Site::class, $project->framework_key);
        $filename = Str::of($project->name)->replace(['/', '\\'], '-') . ' - site establishment data.csv';
        $mainZip
            ->addFromString(
                $filename,
                (new EntityExport($project->sites()->getQuery(), $form))->raw(Excel::CSV)
            );
    }

    private function addNurseriesExports(Project $project, \ZipArchive $mainZip): void
    {
        $form = $this->getForm(Nursery::class, $project->framework_key);
        $filename = Str::of($project->name)->replace(['/', '\\'], '-')  . ' - nursery establishment data.csv';


        $mainZip
            ->addFromString(
                $filename,
                (new EntityExport($project->nurseries()->getQuery(), $form))->raw(Excel::CSV)
            );
    }

    private function getForm(string $modelClass, string $framework)
    {
        return Form::where('model', $modelClass)
            ->where('framework_key', $framework)
            ->firstOrFail();
    }
}
