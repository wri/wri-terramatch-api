<?php

namespace Database\Seeders;

use App\Models\OrganisationDocumentVersion as OrganisationDocumentVersionModel;
use Illuminate\Database\Seeder;

class OrganisationDocumentVersionsTableSeeder extends Seeder
{
    public function run()
    {
        $organisationDocumentVersion = new OrganisationDocumentVersionModel();
        $organisationDocumentVersion->id = 1;
        $organisationDocumentVersion->organisation_document_id = 1;
        $organisationDocumentVersion->status = 'approved';
        $organisationDocumentVersion->name = 'Example Award';
        $organisationDocumentVersion->type = 'award';
        $document = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($organisationDocumentVersion, 'document', $document);
        $organisationDocumentVersion->saveOrFail();

        $organisationDocumentVersion = new OrganisationDocumentVersionModel();
        $organisationDocumentVersion->id = 2;
        $organisationDocumentVersion->organisation_document_id = 1;
        $organisationDocumentVersion->status = 'pending';
        $organisationDocumentVersion->name = 'Example Document';
        $organisationDocumentVersion->type = 'award';
        $document = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($organisationDocumentVersion, 'document', $document);
        $organisationDocumentVersion->saveOrFail();

        $organisationDocumentVersion = new OrganisationDocumentVersionModel();
        $organisationDocumentVersion->id = 3;
        $organisationDocumentVersion->organisation_document_id = 1;
        $organisationDocumentVersion->status = 'rejected';
        $organisationDocumentVersion->name = 'Example Document';
        $organisationDocumentVersion->type = 'award';
        $document = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($organisationDocumentVersion, 'document', $document);
        $organisationDocumentVersion->saveOrFail();
    }
}
