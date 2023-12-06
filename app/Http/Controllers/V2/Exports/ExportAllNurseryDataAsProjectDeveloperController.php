<?php

namespace App\Http\Controllers\V2\Exports;

use App\Exports\V2\EntityExport;
use App\Http\Controllers\Controller;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;

class ExportAllNurseryDataAsProjectDeveloperController extends Controller
{
    public function __invoke(Request $request, Nursery $nursery)
    {
        ini_set('memory_limit', '-1');
        $form = $this->getForm(Nursery::class, $nursery->framework_key);

        $this->authorize('export', [Nursery::class, $form, $nursery->project]);

        $filename = public_path('storage/'.Str::of($nursery->name)->replace(['/', '\\'], '-') .' export - ' . now() . '.zip');
        $zip = new \ZipArchive();
        $zip->open($filename, \ZipArchive::CREATE);

        rescue(function () use ($nursery, $zip, $form) {
            $this->addNurseryExports($nursery, $zip, $form);
        });

        rescue(function () use ($nursery, $zip) {
            $this->addNurseryReportsExports($nursery, $zip);
        });


        $zip->close();

        return response()->download($filename)->deleteFileAfterSend();
    }

    private function addNurseryReportsExports(Nursery $nursery, \ZipArchive $mainZip): void
    {
        $form = $this->getForm(NurseryReport::class, $nursery->framework_key);

        $filename = Str::of($nursery->project->name . ' - '.$nursery->name)->replace(['/', '\\'], '-') . ' - nursery reports.csv';

        $mainZip
            ->addFromString(
                $filename,
                (new EntityExport(NurseryReport::parentId($nursery->id), $form))->raw(Excel::CSV)
            );
    }

    private function addNurseryExports(Nursery $nursery, \ZipArchive $mainZip, Form $form): void
    {
        $filename = Str::of($nursery->project->name . ' - '.$nursery->name)->replace(['/', '\\'], '-') . ' - nursery establishment data.csv';

        $mainZip
            ->addFromString(
                $filename,
                (new EntityExport(Nursery::query()->where('id', $nursery->id), $form))->raw(Excel::CSV)
            );
    }

    private function getForm(string $modelClass, string $framework)
    {
        return Form::where('model', $modelClass)
            ->where('framework_key', $framework)
            ->firstOrFail();
    }
}
