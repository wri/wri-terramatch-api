<?php

use Illuminate\Database\Seeder;
use App\Models\PitchDocument as PitchDocumentModel;

class PitchDocumentsTableSeeder extends Seeder
{
    public function run()
    {
        $pitchDocument = new PitchDocumentModel();
        $pitchDocument->id = 1;
        $pitchDocument->pitch_id = 1;
        $pitchDocument->saveOrFail();
    }
}
