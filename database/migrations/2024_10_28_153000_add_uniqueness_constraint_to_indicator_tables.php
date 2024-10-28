<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('indicator_output_tree_cover_loss', function (Blueprint $table) {
            $table->unique(['polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_hectares', function (Blueprint $table) {
            $table->unique(['polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_tree_count', function (Blueprint $table) {
            $table->unique(['polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_tree_cover', function (Blueprint $table) {
            $table->unique(['polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_field_monitoring', function (Blueprint $table) {
            $table->unique(['polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_msu_carbon', function (Blueprint $table) {
            $table->unique(['polygon_id', 'indicator_slug', 'year_of_analysis'], 'unique_polygon_indicator_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indicator_output_tree_cover_loss', function (Blueprint $table) {
            $table->dropUnique('unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_hectares', function (Blueprint $table) {
            $table->dropUnique('unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_tree_count', function (Blueprint $table) {
            $table->dropUnique('unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_tree_cover', function (Blueprint $table) {
            $table->dropUnique('unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_field_monitoring', function (Blueprint $table) {
            $table->dropUnique('unique_polygon_indicator_year');
        });

        Schema::table('indicator_output_msu_carbon', function (Blueprint $table) {
            $table->dropUnique('unique_polygon_indicator_year');
        });
    }
};
