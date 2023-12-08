<?php

namespace App\Http\Controllers\V2\Applications;

use App\Exports\V2\ApplicationExport;
use App\Http\Controllers\Controller;
use App\Models\V2\Forms\Application;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportApplicationController extends Controller
{
    public function __invoke(Request $request, Application $application): BinaryFileResponse
    {
        $this->authorize('read', $application);

        $filename = 'Application Export - ' . now() . '.csv';

        $query = Application::query()->where('id', $application->id);

        return (new ApplicationExport($query, $application->fundingProgramme))
            ->download($filename, Excel::CSV)->deleteFileAfterSend(true);
    }
}
