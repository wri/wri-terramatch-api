<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpatialIndexToPolygonGeometry extends Migration
{
    /**
       * Run the migrations.
       *
       * @return void
       */
    public function up()
    {
        Schema::table('polygon_geometry', function (Blueprint $table) {
            $table->geometry('geom')->nullable(false)->change();
            // $table->spatialIndex('geom', 'polygon_geometry_geom_spatial_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('polygon_geometry', function (Blueprint $table) {
            // $table->dropIndex('polygon_geometry_geom_spatial_idx');
            $table->geometry('geom')->nullable()->change();
        });
    }
}
