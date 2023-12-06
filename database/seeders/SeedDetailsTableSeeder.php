<?php

namespace Database\Seeders;

use App\Models\SeedDetail;
use Illuminate\Database\Seeder;

class SeedDetailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $seedDetail = new SeedDetail();
        $seedDetail->name = 'test';
        $seedDetail->weight_of_sample = 100000.23;
        $seedDetail->seeds_in_sample = 123;
        $seedDetail->site_id = 1;
        $seedDetail->saveOrFail();
    }
}
