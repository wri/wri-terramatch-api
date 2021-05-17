<?php

use App\Models\Upload as UploadModel;
use Illuminate\Database\Seeder;

class UploadsTableSeeder extends Seeder
{
    public function run()
    {
        $upload = new UploadModel();
        $upload->id = 1;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject("video");
        DatabaseSeeder::setRawAttribute($upload, "location", $location);
        $upload->saveOrFail();
    }
}
