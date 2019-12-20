<?php

use Illuminate\Database\Seeder;
use App\Models\PitchContact as PitchContactModel;

class PitchContactsTableSeeder extends Seeder
{
    public function run()
    {
        $pitchContact = new PitchContactModel();
        $pitchContact->id = 1;
        $pitchContact->pitch_id = 1;
        $pitchContact->user_id = 3;
        $pitchContact->saveOrFail();
    }
}
