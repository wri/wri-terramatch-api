<?php

namespace Database\Seeders;

use App\Models\Aim;
use Illuminate\Database\Seeder;

class AimsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $aim = new Aim();
        $aim->id = 1;
        $aim->programme_id = 1;
        $aim->year_five_trees = 1000;
        $aim->restoration_hectares = 1234;
        $aim->survival_rate = 50;
        $aim->year_five_crown_cover = 1000;
        $aim->saveOrFail();
    }
}
