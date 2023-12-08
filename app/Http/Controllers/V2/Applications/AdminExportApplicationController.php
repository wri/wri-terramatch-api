<?php

namespace App\Http\Controllers\V2\Applications;

use App\Http\Controllers\Controller;
use App\Models\V2\Forms\Application;
use App\Models\V2\FundingProgramme;
use App\Models\V2\SavedExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminExportApplicationController extends Controller
{
    public function __invoke(Request $request, FundingProgramme $fundingProgramme): StreamedResponse
    {
        $this->authorize('exportAll', Application::class);

        $savedExport = SavedExport::where('funding_programme_id', $fundingProgramme->id)->latest()->firstOrFail();

        $url = Storage::disk('s3')->temporaryUrl($savedExport->name, now()->addMinutes(5));

        $csv = Writer::createFromString(file_get_contents($url));

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, str_replace('exports/', '', $savedExport->name), [
            'Content-Type' => 'text/csv',
        ]);
    }
}
