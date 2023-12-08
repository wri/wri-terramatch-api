<?php

namespace Database\Seeders;

use App\Models\PitchDocumentVersion as PitchDocumentVersionModel;
use Illuminate\Database\Seeder;

class PitchDocumentVersionsTableSeeder extends Seeder
{
    public function run()
    {
        $pitchDocumentVersion = new pitchDocumentVersionModel();
        $pitchDocumentVersion->id = 1;
        $pitchDocumentVersion->pitch_document_id = 1;
        $pitchDocumentVersion->approved_rejected_by = 1;
        $pitchDocumentVersion->status = 'approved';
        $pitchDocumentVersion->name = 'Example Document';
        $pitchDocumentVersion->type = 'award';
        $document = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($pitchDocumentVersion, 'document', $document);
        $pitchDocumentVersion->saveOrFail();

        $pitchDocumentVersion = new pitchDocumentVersionModel();
        $pitchDocumentVersion->id = 2;
        $pitchDocumentVersion->pitch_document_id = 1;
        $pitchDocumentVersion->status = 'pending';
        $pitchDocumentVersion->name = 'Example Award';
        $pitchDocumentVersion->type = 'award';
        $document = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($pitchDocumentVersion, 'document', $document);
        $pitchDocumentVersion->saveOrFail();

        $pitchDocumentVersion = new pitchDocumentVersionModel();
        $pitchDocumentVersion->id = 3;
        $pitchDocumentVersion->pitch_document_id = 1;
        $pitchDocumentVersion->status = 'rejected';
        $pitchDocumentVersion->name = 'Example Award';
        $pitchDocumentVersion->type = 'award';
        $document = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($pitchDocumentVersion, 'document', $document);
        $pitchDocumentVersion->saveOrFail();
    }
}
