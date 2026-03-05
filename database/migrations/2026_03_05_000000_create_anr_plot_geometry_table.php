<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     * Storage rationale: the entire FeatureCollection is stored as a single
     * JSON blob (longtext) rather than exploded spatial rows because:
     *   - TM does not validate, generate, or spatially query individual plots.
     *   - ANR plots are served directly from the API as GeoJSON (not via Geoserver).
     *   - A GEOMETRYCOLLECTION spatial type would discard feature properties
     *   - Exploded rows would create ~200× more DB registers per polygon with
     *     no query benefit for this display-only feature.
     */
    public function up(): void
    {
        Schema::create('anr_plot_geometry', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('site_polygon_uuid', 36);
            $table->json('geojson');
            $table->unsignedInteger('plot_count')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['site_polygon_uuid', 'deleted_at'], 'idx_anr_plot_geometry_sp_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anr_plot_geometry');
    }
};
