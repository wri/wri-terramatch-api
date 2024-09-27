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
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->text('invasive_species_removed');
            $table->text('invasive_species_management');
            $table->text('soil_water_restoration_description');
            $table->text('water_structures');
            $table->text('site_community_partners_description');
            $table->text('site_community_partners_income_increase_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->dropColumn('invasive_species_removed');
            $table->dropColumn('invasive_species_management');
            $table->dropColumn('soil_water_restoration_description');
            $table->dropColumn('water_structures');
            $table->dropColumn('site_community_partners_description');
            $table->dropColumn('site_community_partners_income_increase_description');
        });
    }
};
