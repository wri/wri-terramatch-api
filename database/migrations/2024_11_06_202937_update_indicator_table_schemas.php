<?php

use App\Models\V2\Sites\SitePolygon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('indicator_output_tree_cover_loss', function (Blueprint $table) {
            $table->dropIndex('unique_polygon_indicator_year');
            $table->dropColumn('polygon_id');

            $table->foreignIdFor(SitePolygon::class);
            $table->string('indicator_slug')->nullable(false)->change();
            $table->integer('year_of_analysis')->nullable(false)->change();
            $table->json('value')->nullable(false)->change();

            $table->unique(['site_polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_hectares', function (Blueprint $table) {
            $table->dropIndex('unique_polygon_indicator_year');
            $table->dropColumn('polygon_id');

            $table->foreignIdFor(SitePolygon::class);
            $table->string('indicator_slug')->nullable(false)->change();
            $table->integer('year_of_analysis')->nullable(false)->change();
            $table->json('value')->nullable(false)->change();

            $table->unique(['site_polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_tree_count', function (Blueprint $table) {
            $table->dropIndex('unique_polygon_indicator_year');
            $table->dropColumn('polygon_id');

            $table->foreignIdFor(SitePolygon::class);
            $table->string('indicator_slug')->nullable(false)->change();
            $table->integer('year_of_analysis')->nullable(false)->change();

            $table->unique(['site_polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_tree_cover', function (Blueprint $table) {
            $table->dropIndex('unique_polygon_indicator_year');
            $table->dropColumn('polygon_id');

            $table->foreignIdFor(SitePolygon::class);
            $table->string('indicator_slug')->nullable(false)->change();
            $table->integer('year_of_analysis')->nullable(false)->change();

            $table->unique(['site_polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_field_monitoring', function (Blueprint $table) {
            $table->dropIndex('unique_polygon_indicator_year');
            $table->dropColumn('polygon_id');
            $table->dropColumn('value');

            $table->foreignIdFor(SitePolygon::class);
            $table->string('indicator_slug')->nullable(false)->change();
            $table->integer('year_of_analysis')->nullable(false)->change();

            $table->unique(['site_polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_msu_carbon', function (Blueprint $table) {
            $table->dropIndex('unique_polygon_indicator_year');
            $table->dropColumn('polygon_id');

            $table->foreignIdFor(SitePolygon::class);
            $table->string('indicator_slug')->nullable(false)->change();
            $table->integer('year_of_analysis')->nullable(false)->change();

            $table->unique(['site_polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not implemented for this migration
    }
};
