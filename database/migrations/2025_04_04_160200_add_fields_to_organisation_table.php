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
        //
        Schema::table('organisations', function (Blueprint $table) {
            $table->text('consortium')->nullable();
            $table->text('female_youth_leadership_example')->nullable();
            $table->string('level_0_past_restoration')->nullable();
            $table->string('level_1_past_restoration')->nullable();
            $table->string('level_2_past_restoration')->nullable();
            $table->unsignedInteger('trees_naturally_regenerated_total')->nullable();
            $table->unsignedInteger('trees_naturally_regenerated_3year')->nullable();
            $table->tinyInteger('carbon_credits')->nullable();
            $table->text('external_technical_assistance')->nullable();
            $table->text('barriers_to_funding')->nullable();
            $table->text('capacity_building_support_needed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('consortium');
            $table->dropColumn('female_youth_leadership_example');
            $table->dropColumn('level_0_past_restoration');
            $table->dropColumn('level_1_past_restoration');
            $table->dropColumn('level_2_past_restoration');
            $table->dropColumn('trees_naturally_regenerated_total');
            $table->dropColumn('trees_naturally_regenerated_3year');
            $table->dropColumn('carbon_credits');
            $table->dropColumn('external_technical_assistance');
            $table->dropColumn('barriers_to_funding');
            $table->dropColumn('capacity_building_support_needed');
        });
    }
};
