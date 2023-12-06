<?php

namespace Database\Seeders;

use App\Models\SubmissionMediaUpload;
use Illuminate\Database\Seeder;

class SubmissionMediaUploadsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $media = new SubmissionMediaUpload();
        $media->media_title = 'test media upload name';
        $media->is_public = true;
        $media->submission_id = null;
        $media->site_submission_id = 1;
        $media->upload = DatabaseSeeder::seedRandomObject('video');
        $media->saveOrFail();

        $media = new SubmissionMediaUpload();
        $media->media_title = 'test media upload name';
        $media->is_public = true;
        $media->submission_id = null;
        $media->site_submission_id = 1;
        $media->upload = DatabaseSeeder::seedRandomObject('file');
        $media->saveOrFail();

        $media = new SubmissionMediaUpload();
        $media->media_title = 'test media upload name';
        $media->is_public = true;
        $media->submission_id = 1;
        $media->site_submission_id = null;
        $media->upload = DatabaseSeeder::seedRandomObject('video');
        $media->saveOrFail();
    }
}
