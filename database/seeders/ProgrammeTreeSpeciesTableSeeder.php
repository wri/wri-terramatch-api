<?php

namespace Database\Seeders;

use App\Models\ProgrammeTreeSpecies;
use Illuminate\Database\Seeder;

class ProgrammeTreeSpeciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $programme = new ProgrammeTreeSpecies();
        $programme->id = 1;
        $programme->amount = 15;
        $programme->programme_id = 1;
        $programme->programme_submission_id = 1;
        $programme->name = 'A tree species';
        $programme->saveOrFail();


        $programme = new ProgrammeTreeSpecies();
        $programme->id = 2;
        $programme->amount = 25;
        $programme->programme_id = 1;
        $programme->programme_submission_id = 1;
        $programme->csv_import_id = 1;
        $programme->name = 'A tree species';
        $programme->saveOrFail();

        $programme = new ProgrammeTreeSpecies();
        $programme->id = 3;
        $programme->amount = 35;
        $programme->programme_id = 1;
        $programme->programme_submission_id = 1;
        $programme->csv_import_id = 2;
        $programme->name = 'A tree species';
        $programme->saveOrFail();

        $programme = new ProgrammeTreeSpecies();
        $programme->id = 4;
        $programme->amount = 45;
        $programme->programme_id = 1;
        $programme->programme_submission_id = 1;
        $programme->csv_import_id = 2;
        $programme->name = 'Another species';
        $programme->saveOrFail();

        $programme = new ProgrammeTreeSpecies();
        $programme->id = 5;
        $programme->amount = 55;
        $programme->programme_id = 1;
        $programme->programme_submission_id = 1;
        $programme->csv_import_id = 2;
        $programme->name = 'A tree species';
        $programme->saveOrFail();

        $programme = new ProgrammeTreeSpecies();
        $programme->id = 6;
        $programme->amount = 65;
        $programme->programme_id = 1;
        $programme->programme_submission_id = 1;
        $programme->csv_import_id = 3;
        $programme->name = 'A tree species';
        $programme->saveOrFail();

        $programme = new ProgrammeTreeSpecies();
        $programme->id = 7;
        $programme->amount = 75;
        $programme->programme_id = 1;
        $programme->programme_submission_id = 1;
        $programme->csv_import_id = 3;
        $programme->name = 'A tree species';
        $programme->saveOrFail();

        $programme = new ProgrammeTreeSpecies();
        $programme->id = 8;
        $programme->amount = 85;
        $programme->programme_id = 1;
        $programme->programme_submission_id = 1;
        $programme->csv_import_id = 3;
        $programme->name = 'A tree species';
        $programme->saveOrFail();

        $programme = new ProgrammeTreeSpecies();
        $programme->id = 9;
        $programme->amount = 15;
        $programme->programme_id = 1;
        $programme->programme_submission_id = null;
        $programme->name = 'A tree species';
        $programme->saveOrFail();
    }
}
