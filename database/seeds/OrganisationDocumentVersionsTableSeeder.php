<?php

use Illuminate\Database\Seeder;
use App\Models\OrganisationDocumentVersion as OrganisationDocumentVersionModel;
use Illuminate\Support\Facades\Config;

class OrganisationDocumentVersionsTableSeeder extends Seeder
{
    public function run()
    {
        $organisationDocumentVersion = new OrganisationDocumentVersionModel();
        $organisationDocumentVersion->id = 1;
        $organisationDocumentVersion->organisation_document_id = 1;
        $organisationDocumentVersion->status = "approved";
        $organisationDocumentVersion->name = "Example Award";
        $organisationDocumentVersion->type = "award";
        $document = "http://127.0.0.1:9000" . "/" . Config::get("app.s3.bucket") . "/" . DatabaseSeeder::seedRandomObject("file");
        DatabaseSeeder::setRawAttribute($organisationDocumentVersion, "document", $document);
        $organisationDocumentVersion->saveOrFail();

        $organisationDocumentVersion = new OrganisationDocumentVersionModel();
        $organisationDocumentVersion->id = 2;
        $organisationDocumentVersion->organisation_document_id = 1;
        $organisationDocumentVersion->status = "pending";
        $organisationDocumentVersion->name = "Example Document";
        $organisationDocumentVersion->type = "award";
        $document = "http://127.0.0.1:9000" . "/" . Config::get("app.s3.bucket") . "/" . DatabaseSeeder::seedRandomObject("file");
        DatabaseSeeder::setRawAttribute($organisationDocumentVersion, "document", $document);
        $organisationDocumentVersion->saveOrFail();
    }
}
