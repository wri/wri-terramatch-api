<?php

namespace Database\Seeders;

use App\Models\Programme;
use Illuminate\Database\Seeder;

class ProgrammesTableSeeder extends Seeder
{
    public function run()
    {
        $programme = new Programme();
        $programme->id = 1;
        $programme->name = 'Example programme';
        $programme->continent = 'europe';
        $programme->country = 'se';
        $programme->framework_id = 1;
        $programme->organisation_id = 1;
        $programme->saveOrFail();

        $programme = new Programme();
        $programme->id = 2;
        $programme->name = 'Second programme';
        $programme->framework_id = 1;
        $programme->organisation_id = 1;
        $programme->saveOrFail();

        $programme = new Programme();
        $programme->id = 3;
        $programme->name = 'Programme three';
        $programme->framework_id = 1;
        $programme->organisation_id = 1;
        $programme->saveOrFail();

        $programme = new Programme();
        $programme->id = 4;
        $programme->name = 'Programme four';
        $programme->framework_id = 1;
        $programme->organisation_id = 2;
        $programme->saveOrFail();
    }
}
