<?php

namespace Database\Seeders;

use App\Models\SiteTreeSpecies;
use Illuminate\Database\Seeder;

class SiteTreeSpeciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $siteTreeSpecies = new SiteTreeSpecies();
        $siteTreeSpecies->id = 1;
        $siteTreeSpecies->site_id = 1;
        $siteTreeSpecies->amount = 10;
        $siteTreeSpecies->name = 'A tree species';
        $siteTreeSpecies->saveOrFail();

        $siteTreeSpecies = new SiteTreeSpecies();
        $siteTreeSpecies->id = 2;
        $siteTreeSpecies->site_id = 1;
        $siteTreeSpecies->amount = 500;
        $siteTreeSpecies->site_csv_import_id = 1;
        $siteTreeSpecies->name = 'A tree species';
        $siteTreeSpecies->saveOrFail();

        $siteTreeSpecies = new SiteTreeSpecies();
        $siteTreeSpecies->id = 3;
        $siteTreeSpecies->site_submission_id = 1;
        $siteTreeSpecies->site_id = 1;
        $siteTreeSpecies->amount = 250;
        $siteTreeSpecies->name = 'A tree species';
        $siteTreeSpecies->saveOrFail();
    }
}
