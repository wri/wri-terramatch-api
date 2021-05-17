<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTargetsTableLandGeoJson extends Migration
{
    public function up()
    {
        Schema::table("progress_updates", function (Blueprint $table) {
            DB::statement("
                ALTER TABLE `targets` 
                CHANGE COLUMN `land_geojson` `land_geojson` LONGTEXT NULL;
"           );
        });
    }

    public function down()
    {
    }
}
