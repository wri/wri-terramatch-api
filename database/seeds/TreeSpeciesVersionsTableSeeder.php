<?php

use Illuminate\Database\Seeder;
use App\Models\TreeSpeciesVersion as TreeSpeciesVersionModel;

class TreeSpeciesVersionsTableSeeder extends Seeder
{
    public function run()
    {
        $treeSpeciesVersion = new TreeSpeciesVersionModel();
        $treeSpeciesVersion->id = 1;
        $treeSpeciesVersion->status = "approved";
        $treeSpeciesVersion->tree_species_id = 1;
        $treeSpeciesVersion->name = "Conker";
        $treeSpeciesVersion->is_native = false;
        $treeSpeciesVersion->count = 250;
        $treeSpeciesVersion->price_to_plant = 1;
        $treeSpeciesVersion->price_to_maintain = 0.25;
        $treeSpeciesVersion->owner = "community";
        $treeSpeciesVersion->season = "winter";
        $treeSpeciesVersion->saplings = 20.40;
        $treeSpeciesVersion->site_prep = 50.40;
        $treeSpeciesVersion->saveOrFail();

        $treeSpeciesVersion = new TreeSpeciesVersionModel();
        $treeSpeciesVersion->id = 2;
        $treeSpeciesVersion->status = "pending";
        $treeSpeciesVersion->tree_species_id = 1;
        $treeSpeciesVersion->name = "Monkey";
        $treeSpeciesVersion->is_native = false;
        $treeSpeciesVersion->count = 125;
        $treeSpeciesVersion->price_to_plant = 1;
        $treeSpeciesVersion->price_to_maintain = 0.25;
        $treeSpeciesVersion->owner = "community";
        $treeSpeciesVersion->season = "summer";
        $treeSpeciesVersion->saplings = 20.40;
        $treeSpeciesVersion->site_prep = 50.40;
        $treeSpeciesVersion->saveOrFail();
    }
}
