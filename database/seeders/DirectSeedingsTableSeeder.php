<?php

namespace Database\Seeders;

use App\Models\DirectSeeding;
use Illuminate\Database\Seeder;

class DirectSeedingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $directSeeding = new DirectSeeding();
        $directSeeding->name = 'Birch';
        $directSeeding->weight = 100;
        $directSeeding->site_submission_id = 1;
        $directSeeding->saveOrFail();

        $directSeeding = new DirectSeeding();
        $directSeeding->name = 'Acer';
        $directSeeding->weight = 100;
        $directSeeding->site_submission_id = 1;
        $directSeeding->saveOrFail();

        $directSeeding = new DirectSeeding();
        $directSeeding->name = 'Birch';
        $directSeeding->weight = 200;
        $directSeeding->site_submission_id = 1;
        $directSeeding->saveOrFail();
    }
}
