<?php

namespace Database\Seeders;

use App\Models\Terrafund\TerrafundNursery;
use Illuminate\Database\Seeder;

class TerrafundNurseriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $nursery = new TerrafundNursery();
        $nursery->name = 'Terrafund Nursery';
        $nursery->start_date = '2020-01-01';
        $nursery->end_date = '2021-01-01';
        $nursery->seedling_grown = 123;
        $nursery->planting_contribution = 'planting contribution';
        $nursery->nursery_type = 'existing';
        $nursery->terrafund_programme_id = 1;
        $nursery->saveOrFail();
    }
}
