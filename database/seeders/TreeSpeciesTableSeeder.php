<?php

namespace Database\Seeders;

use App\Models\TreeSpecies as TreeSpeciesModel;
use Illuminate\Database\Seeder;

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
