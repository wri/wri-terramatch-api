<?php

namespace Database\Seeders;

use App\Models\PitchDocument as PitchDocumentModel;
use Illuminate\Database\Seeder;

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
