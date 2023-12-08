<?php

namespace Database\Seeders;

use App\Models\DocumentFile;
use App\Models\SiteSubmission;
use App\Models\Submission;
use Illuminate\Database\Seeder;

class DocumentFilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $documentFile = new DocumentFile();
        $documentFile->title = 'test file 1';
        $documentFile->is_public = true;
        $documentFile->document_fileable_id = 1;
        $documentFile->document_fileable_type = SiteSubmission::class;
        $documentFile->upload = DatabaseSeeder::seedRandomObject('file');
        $documentFile->saveOrFail();

        $documentFile = new DocumentFile();
        $documentFile->title = 'tree species test file';
        $documentFile->is_public = true;
        $documentFile->collection = 'tree_species';
        $documentFile->document_fileable_id = 1;
        $documentFile->document_fileable_type = Submission::class;
        $documentFile->upload = DatabaseSeeder::seedRandomObject('file');
        $documentFile->saveOrFail();
    }
}
