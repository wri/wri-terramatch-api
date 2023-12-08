<?php

namespace Database\Seeders;

use App\Models\OrganisationDocument as OrganisationDocumentModel;
use Illuminate\Database\Seeder;

class OrganisationDocumentsTableSeeder extends Seeder
{
    public function run()
    {
        $organisationDocument = new OrganisationDocumentModel();
        $organisationDocument->id = 1;
        $organisationDocument->organisation_id = 1;
        $organisationDocument->saveOrFail();
    }
}
