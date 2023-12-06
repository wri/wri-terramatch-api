<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class StagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Artisan::call('v2-custom-form-update-data');
        Artisan::call('v2-custom-form-prep-phase2');
        Artisan::call('v2-custom-form-rfp-update-data');
        Artisan::call('v2migration:roles');
    }
}
