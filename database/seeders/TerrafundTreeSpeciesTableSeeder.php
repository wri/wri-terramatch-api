<?php

namespace Database\Seeders;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundProgramme;
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
        $treeSpecies->id = 1;
        $treeSpecies->name = 'name';
        $treeSpecies->amount = 123;
        $treeSpecies->treeable_type = TerrafundProgramme::class;
        $treeSpecies->treeable_id = 1;
        $treeSpecies->saveOrFail();

        $treeSpecies = new TerrafundTreeSpecies();
        $treeSpecies->id = 2;
        $treeSpecies->name = 'another name';
        $treeSpecies->amount = 321;
        $treeSpecies->treeable_type = TerrafundProgramme::class;
        $treeSpecies->treeable_id = 1;
        $treeSpecies->terrafund_csv_import_id = 1;
        $treeSpecies->saveOrFail();

        $treeSpecies = new TerrafundTreeSpecies();
        $treeSpecies->id = 3;
        $treeSpecies->name = 'nursery species';
        $treeSpecies->amount = 100;
        $treeSpecies->treeable_type = TerrafundNursery::class;
        $treeSpecies->treeable_id = 1;
        $treeSpecies->saveOrFail();
    }
}
