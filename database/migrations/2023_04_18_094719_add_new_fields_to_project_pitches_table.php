<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToProjectPitchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->date('expected_active_restoration_start_date')->nullable();
            $table->date('expected_active_restoration_end_date')->nullable();
            $table->text('description_of_project_timeline')->nullable();
            $table->text('proj_partner_info')->nullable();
            $table->text('land_tenure_proj_area')->nullable();
            $table->text('landholder_comm_engage')->nullable();
            $table->text('proj_success_risks')->nullable();
            $table->text('monitor_eval_plan')->nullable();
            $table->text('proj_boundary')->nullable();
            $table->text('sustainable_dev_goals')->nullable();
            $table->text('proj_area_description')->nullable();
            $table->text('environmental_goals')->nullable();
            $table->unsignedInteger('proposed_num_sites')->nullable();
            $table->unsignedInteger('proposed_num_nurseries')->nullable();
            $table->text('curr_land_degradation')->nullable();
            $table->text('proj_impact_socieconom')->nullable();
            $table->text('proj_impact_foodsec')->nullable();
            $table->text('proj_impact_watersec')->nullable();
            $table->text('proj_impact_jobtypes')->nullable();
            $table->unsignedInteger('num_jobs_created')->nullable();
            $table->tinyInteger('pct_employees_men')->nullable();
            $table->tinyInteger('pct_employees_women')->nullable();
            $table->tinyInteger('pct_employees_18to35')->nullable();
            $table->tinyInteger('pct_employees_older35')->nullable();
            $table->unsignedInteger('proj_beneficiaries')->nullable();
            $table->tinyInteger('pct_beneficiaries_women')->nullable();
            $table->tinyInteger('pct_beneficiaries_small')->nullable();
            $table->tinyInteger('pct_beneficiaries_large')->nullable();
            $table->tinyInteger('pct_beneficiaries_35below')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->dropColumn([
                'expected_active_restoration_start_date',
                'expected_active_restoration_end_date',
                'description_of_project_timeline',
                'proj_partner_info',
                'land_tenure_proj_area',
                'landholder_comm_engage',
                'proj_success_risks',
                'monitor_eval_plan',
                'proj_boundary',
                'sustainable_dev_goals',
                'proj_area_description',
                'proposed_num_sites',
                'environmental_goals',
                'proposed_num_nurseries',
                'curr_land_degradation',
                'proj_impact_socieconom',
                'proj_impact_foodsec',
                'proj_impact_watersec',
                'proj_impact_jobtypes',
                'num_jobs_created',
                'pct_employees_men',
                'pct_employees_women',
                'pct_employees_18to35',
                'pct_employees_older35',
                'proj_beneficiaries',
                'pct_beneficiaries_women',
                'pct_beneficiaries_small',
                'pct_beneficiaries_large',
                'pct_beneficiaries_35below',
            ]);
        });
    }
}
