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
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->string('level_0_proposed')->nullable();
            $table->string('level_2_proposed')->nullable();
            $table->string('level_1_proposed')->nullable();
            $table->decimal('lat_proposed', 10, 8)->nullable();
            $table->decimal('long_proposed', 10, 8)->nullable();
            $table->text('stakeholder_engagement')->nullable();
            $table->string('landowner_agreement')->nullable();
            $table->text('landowner_agreement_description')->nullable();
            $table->text('land_tenure_distribution')->nullable();
            $table->text('land_tenure_risks')->nullable();
            $table->text('non_tree_interventions_description')->nullable();
            $table->text('complement_existing_restoration')->nullable();
            $table->text('land_use_type_distribution')->nullable();
            $table->text('restoration_strategy_distribution')->nullable();
            $table->integer('total_tree_second_yr')->nullable();
            $table->unsignedInteger('proj_survival_rate')->nullable();
            $table->text('anr_approach')->nullable();
            $table->text('anr_rights')->nullable();
            $table->text('project_site_model')->nullable();
            $table->text('indigenous_impact')->nullable();
            $table->string('barriers_project_activity')->nullable();
            $table->text('barriers_project_activity_description')->nullable();
            $table->text('other_engage_women_youth')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->dropColumn('level_0_proposed');
            $table->dropColumn('level_2_proposed');
            $table->dropColumn('level_1_proposed');
            $table->dropColumn('lat_proposed');
            $table->dropColumn('long_proposed');
            $table->dropColumn('stakeholder_engagement');
            $table->dropColumn('landowner_agreement');
            $table->dropColumn('landowner_agreement_description');
            $table->dropColumn('land_tenure_distribution');
            $table->dropColumn('land_tenure_risks');
            $table->dropColumn('non_tree_interventions_description');
            $table->dropColumn('complement_existing_restoration');
            $table->dropColumn('land_use_type_distribution');
            $table->dropColumn('restoration_strategy_distribution');
            $table->dropColumn('total_tree_second_yr');
            $table->dropColumn('proj_survival_rate');
            $table->dropColumn('anr_approach');
            $table->dropColumn('anr_rights');
            $table->dropColumn('project_site_model');
            $table->dropColumn('indigenous_impact');
            $table->dropColumn('barriers_project_activity');
            $table->dropColumn('barriers_project_activity_description');
            $table->dropColumn('other_engage_women_youth');
        });
    }
};
