<?php

namespace Database\Seeders;

use App\Models\Terrafund\TerrafundFile;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundSite;
use Illuminate\Database\Seeder;

class TerrafundFilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {

        $file = new TerrafundFile();
        $file->fileable_type = TerrafundNursery::class;
        $file->fileable_id = 1;
        $file->upload = DatabaseSeeder::seedRandomObject('image');
        $file->is_public = true;
        $file->saveOrFail();

        $file = new TerrafundFile();
        $file->fileable_type = TerrafundSite::class;
        $file->fileable_id = 1;
        $file->upload = DatabaseSeeder::seedRandomObject('image');
        $file->is_public = true;
        $file->saveOrFail();
    }
}
