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
        Schema::create('anr_plot_geometries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('site_polygon_id');
            $table->json('geojson');
            $table->unsignedInteger('plot_count')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['site_polygon_id', 'deleted_at'], 'idx_anr_plot_geometries_site_polygon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anr_plot_geometries');
    }
};
