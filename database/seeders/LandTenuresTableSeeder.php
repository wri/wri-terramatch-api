<?php

namespace Database\Seeders;

use App\Models\LandTenure;
use Illuminate\Database\Seeder;

class LandTenuresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $landTenure = new LandTenure();
        $landTenure->id = 1;
        $landTenure->name = 'Public';
        $landTenure->key = 'public';
        $landTenure->saveOrFail();

        $landTenure = new LandTenure();
        $landTenure->id = 2;
        $landTenure->name = 'Private';
        $landTenure->key = 'private';
        $landTenure->saveOrFail();
    }
}
