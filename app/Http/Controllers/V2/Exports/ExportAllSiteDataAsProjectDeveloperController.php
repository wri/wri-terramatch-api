<?php

namespace App\Http\Controllers\V2\Exports;

use App\Exports\V2\EntityExport;
use App\Http\Controllers\Controller;
use App\Models\V2\Forms\Form;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;

class ExportAllSiteDataAsProjectDeveloperController extends Controller
{
    public function __invoke(Request $request, Site $site)
    {
        //        ini_set('memory_limit', '512M');
        $form = $this->getForm(Site::class, $site->framework_key);

        $this->authorize('export', [Site::class, $form, $site->project]);

        $filename = public_path('storage/' . Str::of($site->name)->replace(['/', '\\'], '-') . ' export - ' . now() . '.zip');
        $zip = new \ZipArchive();
        $zip->open($filename, \ZipArchive::CREATE);

        rescue(function () use ($site, $zip, $form) {
            $this->addSiteExports($site, $zip, $form);
        });

        rescue(function () use ($site, $zip) {
            $this->addSiteReportsExports($site, $zip);
        });


        $zip->close();

        return response()->download($filename)->deleteFileAfterSend();
    }

    private function addSiteReportsExports(Site $site, \ZipArchive $mainZip): void
    {
        $form = $this->getForm(SiteReport::class, $site->framework_key);
        $filename = Str::of($site->project->name . ' - ' . $site->name)->replace(['/', '\\'], '-') . ' - site reports.csv';

        $mainZip
            ->addFromString(
                $filename,
                (new EntityExport(SiteReport::parentId($site->id), $form))->raw(Excel::CSV)
            );
    }

    private function addSiteExports(Site $site, \ZipArchive $mainZip, Form $form): void
    {
        $filename = Str::of($site->project->name . ' - ' . $site->name)->replace(['/', '\\'], '-') . ' - site establishment data.csv';

        $mainZip
            ->addFromString(
                $filename,
                (new EntityExport(Site::query()->where('id', $site->id), $form))->raw(Excel::CSV)
            );
    }

    private function getForm(string $modelClass, string $framework)
    {
        return Form::where('model', $modelClass)
            ->where('framework_key', $framework)
            ->firstOrFail();
    }
}
