<?php

namespace Database\Seeders;

use App\Models\OrganisationFile;
use Illuminate\Database\Seeder;

class OrganisationFilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $file = new OrganisationFile();
        $file->organisation_id = 1;
        $file->upload = DatabaseSeeder::seedRandomObject('file');
        $file->type = 'financial_statement';
        $file->saveOrFail();

        $file = new OrganisationFile();
        $file->organisation_id = 2;
        $file->upload = DatabaseSeeder::seedRandomObject('file');
        $file->type = 'financial_statement';
        $file->saveOrFail();
    }
}
