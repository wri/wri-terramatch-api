<?php

namespace Database\Seeders;

use App\Models\SiteRestorationMethod;
use Illuminate\Database\Seeder;

class SiteRestorationMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $siteRestorationMethod = new SiteRestorationMethod();
        $siteRestorationMethod->id = 1;
        $siteRestorationMethod->name = 'Mangrove Tree Restoration';
        $siteRestorationMethod->key = 'mangrove_tree_restoration';
        $siteRestorationMethod->saveOrFail();

        $siteRestorationMethod = new SiteRestorationMethod();
        $siteRestorationMethod->id = 2;
        $siteRestorationMethod->name = 'Assisted Natural Regeneration';
        $siteRestorationMethod->key = 'assisted_natural_regeneration';
        $siteRestorationMethod->saveOrFail();
    }
}
