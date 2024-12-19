<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            DB::statement('ALTER TABLE polygon_geometry MODIFY COLUMN geom GEOMETRY NOT NULL');
            DB::statement('CREATE SPATIAL INDEX polygon_geometry_geom_spatial_idx ON polygon_geometry (geom)');
        });
    }

    public function down()
    {
        Schema::table('polygon_geometry', function (Blueprint $table) {
            DB::statement('DROP INDEX polygon_geometry_geom_spatial_idx ON polygon_geometry');
            DB::statement('ALTER TABLE polygon_geometry MODIFY COLUMN geom GEOMETRY NULL');
        });
    }
}
