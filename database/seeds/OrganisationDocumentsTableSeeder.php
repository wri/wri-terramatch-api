<?php

use Illuminate\Database\Seeder;
use App\Models\OrganisationDocument as OrganisationDocumentModel;

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
