<?php

use App\Jobs\UpdatePricePerTreeJob;
use App\Models\Pitch as PitchModel;
use Illuminate\Database\Migrations\Migration;

class SetPricePerTreeOnPitchVersions extends Migration
{
    public function up()
    {
        $pitches = PitchModel::get();
        foreach ($pitches as $pitch) {
            UpdatePricePerTreeJob::dispatchNow($pitch);
        }
    }

    public function down()
    {
    }
}
