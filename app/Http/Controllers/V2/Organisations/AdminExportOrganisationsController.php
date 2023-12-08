<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Exports\V2\OrganisationsExport;
use App\Http\Controllers\Controller;
use App\Models\V2\Organisation;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminExportOrganisationsController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $this->authorize('export', Organisation::class);

        $filename = 'organisations(' . now()->format('d-m-Y-H-i'). ').csv';

        return (new OrganisationsExport())->download($filename, Excel::CSV)->deleteFileAfterSend(true);
    }
}
