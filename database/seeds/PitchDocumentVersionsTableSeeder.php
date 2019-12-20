<?php

use Illuminate\Database\Seeder;
use App\Models\PitchDocumentVersion as PitchDocumentVersionModel;
use Illuminate\Support\Facades\Config;

class PitchDocumentVersionsTableSeeder extends Seeder
{
    public function run()
    {
        $pitchDocumentVersion = new pitchDocumentVersionModel();
        $pitchDocumentVersion->id = 1;
        $pitchDocumentVersion->pitch_document_id = 1;
        $pitchDocumentVersion->status = "approved";
        $pitchDocumentVersion->name = "Example Document";
        $pitchDocumentVersion->type = "award";
        $document = "http://127.0.0.1:9000" . "/" . Config::get("app.s3.bucket") . "/" . DatabaseSeeder::seedRandomObject("file");
        DatabaseSeeder::setRawAttribute($pitchDocumentVersion, "document", $document);
        $pitchDocumentVersion->saveOrFail();

        $pitchDocumentVersion = new pitchDocumentVersionModel();
        $pitchDocumentVersion->id = 2;
        $pitchDocumentVersion->pitch_document_id = 1;
        $pitchDocumentVersion->status = "pending";
        $pitchDocumentVersion->name = "Example Award";
        $pitchDocumentVersion->type = "award";
        $document = "http://127.0.0.1:9000" . "/" . Config::get("app.s3.bucket") ."/" . DatabaseSeeder::seedRandomObject("file");
        DatabaseSeeder::setRawAttribute($pitchDocumentVersion, "document", $document);
        $pitchDocumentVersion->saveOrFail();
    }
}
