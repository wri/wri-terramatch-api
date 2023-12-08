<?php

namespace Database\Seeders;

use App\Models\Site as SiteModel;
use Illuminate\Database\Seeder;

class SitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $site = new SiteModel();
        $site->id = 1;
        $site->programme_id = 1;
        $site->name = 'Some Site';
        $site->description = 'A site, somewhere';
        $site->end_date = '2098-04-24';
        $site->control_site = false;
        $site->saveOrFail();

        $site = new SiteModel();
        $site->id = 2;
        $site->programme_id = 1;
        $site->name = 'Some Site';
        $site->description = 'A site, somewhere';
        $site->technical_narrative = 'tech narrative';
        $site->public_narrative = 'public narrative';
        $site->control_site = false;
        $site->saveOrFail();

        $site = new SiteModel();
        $site->id = 3;
        $site->programme_id = 1;
        $site->name = 'Some Site';
        $site->description = 'A site, somewhere';
        $site->control_site = false;
        $site->saveOrFail();

        $site = new SiteModel();
        $site->id = 4;
        $site->programme_id = 1;
        $site->name = 'Some Site';
        $site->description = 'A site, somewhere';
        $site->control_site = false;
        $site->saveOrFail();

        $site = new SiteModel();
        $site->id = 5;
        $site->programme_id = 1;
        $site->name = 'Some Site';
        $site->description = 'A site, somewhere';
        $site->control_site = false;
        $site->saveOrFail();

        $site = new SiteModel();
        $site->id = 6;
        $site->programme_id = 1;
        $site->name = 'Some Site';
        $site->description = 'A site, somewhere';
        $site->control_site = false;
        $site->saveOrFail();

        $site = new SiteModel();
        $site->id = 7;
        $site->programme_id = 4;
        $site->name = 'Some site belonging to someone else';
        $site->description = 'A site, somewhere';
        $site->control_site = false;
        $site->saveOrFail();

        $site = new SiteModel();
        $site->id = 8;
        $site->programme_id = 1;
        $site->name = 'A control site';
        $site->description = 'A control site';
        $site->control_site = true;
        $site->saveOrFail();
    }
}
