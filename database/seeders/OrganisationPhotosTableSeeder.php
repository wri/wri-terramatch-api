<?php

namespace Database\Seeders;

use App\Models\OrganisationPhoto;
use Illuminate\Database\Seeder;

class OrganisationPhotosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $file = new OrganisationPhoto();
        $file->organisation_id = 1;
        $file->upload = DatabaseSeeder::seedRandomObject('image');
        $file->is_public = true;
        $file->saveOrFail();
    }
}
