<?php

namespace App\Services;

use Maatwebsite\Excel\Excel;
use App\Exports\V2\OrganisationsExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportAllOrganisationsService
{
    public function run($filename): BinaryFileResponse
    {
        return (new OrganisationsExport())->download($filename, Excel::CSV)->deleteFileAfterSend(true);
    }
}
