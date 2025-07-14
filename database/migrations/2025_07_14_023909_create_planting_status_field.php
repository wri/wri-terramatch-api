<?php

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
        // Add planting_status to v2_projects
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->enum('planting_status', [
                'no-restoration-expected',
                'not-started',
                'in-progress',
                'replacement-planting',
                'completed'
            ])->nullable()->after('status');
        });

        // Add planting_status to v2_sites
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->enum('planting_status', [
                'no-restoration-expected',
                'not-started',
                'in-progress',
                'replacement-planting',
                'completed'
            ])->nullable()->after('status');
        });

        // Add planting_status to site_polygon (includes 'disturbed' for polygons only)
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->enum('planting_status', [
                'no-restoration-expected',
                'not-started',
                'in-progress',
                'disturbed',
                'replacement-planting',
                'completed'
            ])->nullable()->after('status');
        });

        // Add planting_status to v2_nurseries
        Schema::table('v2_nurseries', function (Blueprint $table) {
            $table->enum('planting_status', [
                'no-restoration-expected',
                'not-started',
                'in-progress',
                'replacement-planting',
                'completed'
            ])->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->dropColumn('planting_status');
        });

        Schema::table('v2_sites', function (Blueprint $table) {
            $table->dropColumn('planting_status');
        });

        Schema::table('site_polygon', function (Blueprint $table) {
            $table->dropColumn('planting_status');
        });

        Schema::table('v2_nurseries', function (Blueprint $table) {
            $table->dropColumn('planting_status');
        });
    }
};
