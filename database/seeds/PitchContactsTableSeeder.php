<?php

use App\Models\PitchContact as PitchContactModel;
use Illuminate\Database\Seeder;

class PitchContactsTableSeeder extends Seeder
{
    public function run()
    {
        $pitchContact = new PitchContactModel();
        $pitchContact->id = 1;
        $pitchContact->pitch_id = 1;
        $pitchContact->user_id = 3;
        $pitchContact->saveOrFail();

        $pitchContact = new PitchContactModel();
        $pitchContact->id = 2;
        $pitchContact->pitch_id = 2;
        $pitchContact->user_id = 3;
        $pitchContact->saveOrFail();
    }
}
