<?php

use Illuminate\Database\Seeder;
use App\Models\Pitch as PitchModel;

class PitchesTableSeeder extends Seeder
{
    public function run()
    {
        $pitch = new PitchModel();
        $pitch->id = 1;
        $pitch->organisation_id = 1;
        $pitch->saveOrFail();
    }
}
