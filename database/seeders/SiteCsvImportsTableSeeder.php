<?php

namespace Database\Seeders;

use App\Models\SiteCsvImport;
use Illuminate\Database\Seeder;

class SiteCsvImportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $siteCsvImport = new SiteCsvImport();
        $siteCsvImport->id = 1;
        $siteCsvImport->site_id = 1;
        $siteCsvImport->total_rows = 10;
        $siteCsvImport->saveOrFail();
    }
}
