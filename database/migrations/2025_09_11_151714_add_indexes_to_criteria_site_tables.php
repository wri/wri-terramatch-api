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
        Schema::table('criteria_site', function (Blueprint $table) {
            $table->index('polygon_id', 'criteria_site_polygon_id_index');
            $table->index('criteria_id', 'criteria_site_criteria_id_index');
        });

        Schema::table('criteria_site_historic', function (Blueprint $table) {
            $table->index('polygon_id', 'criteria_site_historic_polygon_id_index');
            $table->index('criteria_id', 'criteria_site_historic_criteria_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('criteria_site', function (Blueprint $table) {
            $table->dropIndex('criteria_site_polygon_id_index');
            $table->dropIndex('criteria_site_criteria_id_index');
        });

        Schema::table('criteria_site_historic', function (Blueprint $table) {
            $table->dropIndex('criteria_site_historic_polygon_id_index');
            $table->dropIndex('criteria_site_historic_criteria_id_index');
        });
    }
};
