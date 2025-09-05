<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    { 
        
        // Modificar columna para que sea NOT NULL (sin SRID en MariaDB)
        DB::statement('ALTER TABLE point_geometry MODIFY geom GEOMETRY NOT NULL');
        
        // Crear índice espacial
        DB::statement('CREATE SPATIAL INDEX point_geometry_geom_spatial_idx ON point_geometry(geom)');
        
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->index('point_id', 'idx_site_polygon_point_id');
        });
    }

    public function down(): void
    {
        DB::statement('DROP INDEX point_geometry_geom_spatial_idx ON point_geometry');
        DB::statement('ALTER TABLE point_geometry MODIFY geom GEOMETRY NULL');
        Schema::table('site_polygon', function (Blueprint $table) { 
            $table->dropIndex('idx_site_polygon_point_id');
        });
    }
};
