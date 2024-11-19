<?php

namespace App\Services;

use App\Exports\V2\OrganisationsExport;

class ExportAllOrganisationsService
{
    public function run()
    {
        return new OrganisationsExport();
        // return file_get_contents($filename);
    }
}
