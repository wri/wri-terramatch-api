<?php

namespace Database\Seeders;

use App\Models\Terrafund\TerrafundSiteSubmission;
use Illuminate\Database\Seeder;

class TerrafundSiteSubmissionsTableSeeder extends Seeder
{
    public function run()
    {
        $siteSubmission = new TerrafundSiteSubmission();
        $siteSubmission->terrafund_site_id = 1;
        $siteSubmission->saveOrFail();
    }
}
