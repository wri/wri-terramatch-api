<?php

namespace Database\Seeders;

use App\Models\Terrafund\TerrafundSite;
use Illuminate\Database\Seeder;

class TerrafundSitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $terrafundSite = new TerrafundSite();
        $terrafundSite->name = 'Terrafund Site';
        $terrafundSite->start_date = '2020-01-01';
        $terrafundSite->end_date = '2021-01-01';
        $terrafundSite->saveOrFail();
    }
}
