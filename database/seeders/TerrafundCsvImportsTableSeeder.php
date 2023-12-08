<?php

namespace Database\Seeders;

use App\Models\Terrafund\TerrafundCsvImport;
use App\Models\Terrafund\TerrafundProgramme;
use Illuminate\Database\Seeder;

class TerrafundCsvImportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $csvImport = new TerrafundCsvImport();
        $csvImport->id = 1;
        $csvImport->importable_type = TerrafundProgramme::class;
        $csvImport->importable_id = 1;
        $csvImport->total_rows = 10;
        $csvImport->saveOrFail();
    }
}
