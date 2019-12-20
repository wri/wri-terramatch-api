<?php

use Illuminate\Database\Seeder;
use App\Models\TreeSpecies as TreeSpeciesModel;

class TreeSpeciesTableSeeder extends Seeder
{
    public function run()
    {
        $treeSpecies = new TreeSpeciesModel();
        $treeSpecies->id = 1;
        $treeSpecies->pitch_id = 1;
        $treeSpecies->saveOrFail();
    }
}
