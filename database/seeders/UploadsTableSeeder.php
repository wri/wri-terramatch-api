<?php

namespace Database\Seeders;

use App\Models\Upload as UploadModel;
use Illuminate\Database\Seeder;

class UploadsTableSeeder extends Seeder
{
    public function run()
    {
        $upload = new UploadModel();
        $upload->id = 1;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('video');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 2;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('video');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 3;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('video');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 4;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('video');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 5;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('video');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 6;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('video');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 7;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('video');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 8;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 9;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 10;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('video');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 11;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('image');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 12;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 13;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('image');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 14;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('image');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 15;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 16;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 17;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 18;
        $upload->user_id = 12;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 19;
        $upload->user_id = 12;
        $location = DatabaseSeeder::seedRandomObject('image');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 20;
        $upload->user_id = 1;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 21;
        $upload->user_id = 13;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 22;
        $upload->user_id = 13;
        $location = DatabaseSeeder::seedRandomObject('image');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 23;
        $upload->user_id = 13;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 24;
        $upload->user_id = 12;
        $location = DatabaseSeeder::seedRandomObject('image');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 25;
        $upload->user_id = 12;
        $location = DatabaseSeeder::seedRandomObject('image');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        foreach (range(27, 36) as $uploadId) {
            $upload = new UploadModel();
            $upload->id = $uploadId;
            $upload->user_id = 12;
            $location = DatabaseSeeder::seedRandomObject('image');
            DatabaseSeeder::setRawAttribute($upload, 'location', $location);
            $upload->saveOrFail();
        }

        $upload = new UploadModel();
        $upload->id = 37;
        $upload->user_id = 13;
        $location = DatabaseSeeder::seedRandomObject('image');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 38;
        $upload->user_id = 13;
        $location = DatabaseSeeder::seedRandomObject('image');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 39;
        $upload->user_id = 12;
        $location = DatabaseSeeder::seedRandomObject('image');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 40;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 41;
        $upload->user_id = 3;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();

        $upload = new UploadModel();
        $upload->id = 42;
        $upload->user_id = 12;
        $location = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($upload, 'location', $location);
        $upload->saveOrFail();
    }
}
