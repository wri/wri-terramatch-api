<?php

namespace Database\Seeders;

use App\Models\MediaUpload;
use Illuminate\Database\Seeder;

class MediaUploadsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $media = new MediaUpload();
        $media->media_title = 'test media upload name';
        $media->is_public = true;
        $media->programme_id = 1;
        $media->upload = DatabaseSeeder::seedRandomObject('image');
        $media->saveOrFail();

        $media = new MediaUpload();
        $media->media_title = 'test site media upload name';
        $media->is_public = true;
        $media->site_id = 1;
        $media->upload = DatabaseSeeder::seedRandomObject('image');
        $media->saveOrFail();
    }
}
