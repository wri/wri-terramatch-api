<?php

namespace Database\Seeders;

use App\Models\Terrafund\TerrafundDisturbance;
use App\Models\Terrafund\TerrafundSiteSubmission;
use Illuminate\Database\Seeder;

class TerrafundDisturbancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $disturbance = new TerrafundDisturbance();
        $disturbance->type = 'manmade';
        $disturbance->description = 'A disturbance';
        $disturbance->disturbanceable_type = TerrafundSiteSubmission::class;
        $disturbance->disturbanceable_id = 1;
        $disturbance->saveOrFail();
    }
}
