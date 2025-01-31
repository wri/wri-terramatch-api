<?php

namespace Database\Seeders;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundTreeSpecies;
use Illuminate\Database\Seeder;

class TerrafundTreeSpeciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $treeSpecies = new TerrafundTreeSpecies();
        $treeSpecies->id = 3;
        $treeSpecies->name = 'nursery species';
        $treeSpecies->amount = 100;
        $treeSpecies->treeable_type = TerrafundNursery::class;
        $treeSpecies->treeable_id = 1;
        $treeSpecies->saveOrFail();
    }
}
