<?php

namespace Database\Seeders;

use App\Models\CsvImport;
use Illuminate\Database\Seeder;

class CsvImportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $programme = new CsvImport();
        $programme->id = 1;
        $programme->programme_id = 1;
        $programme->programme_submission_id = 1;
        $programme->total_rows = 10;
        $programme->saveOrFail();

        $programme = new CsvImport();
        $programme->id = 2;
        $programme->programme_id = 1;
        $programme->programme_submission_id = 1;
        $programme->status = 'completed';
        $programme->total_rows = 3;
        $programme->saveOrFail();

        $programme = new CsvImport();
        $programme->id = 3;
        $programme->programme_id = 1;
        $programme->programme_submission_id = 1;
        $programme->status = 'failed';
        $programme->total_rows = 5;
        $programme->saveOrFail();
    }
}
