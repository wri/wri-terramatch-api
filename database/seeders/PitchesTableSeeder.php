<?php

namespace Database\Seeders;

use App\Models\Pitch as PitchModel;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Seeder;

class PitchesTableSeeder extends Seeder
{
    public function run()
    {
        $pitch = new PitchModel();
        $pitch->id = 1;
        $pitch->organisation_id = 1;
        $pitch->visibility_updated_at = new DateTime('now', new DateTimeZone('UTC'));
        $pitch->saveOrFail();

        $pitch = new PitchModel();
        $pitch->id = 2;
        $pitch->organisation_id = 1;
        $pitch->visibility = 'fully_invested_funded';
        $pitch->visibility_updated_at = new DateTime('now', new DateTimeZone('UTC'));
        $pitch->saveOrFail();
    }
}
