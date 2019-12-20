<?php

use Illuminate\Support\Facades\DB;
use App\Jobs\UpdatePricePerTreeJob;
use Illuminate\Database\Migrations\Migration;

class SetPricePerTreeOnPitchVersions extends Migration
{
    public function up()
    {
        $pitches = DB::table("pitches")->get();
        foreach ($pitches as $pitch) {
            UpdatePricePerTreeJob::dispatchNow($pitch->id);
        }
    }

    public function down()
    {
    }
}
