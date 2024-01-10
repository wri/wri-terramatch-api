<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToV2ProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // mediumText string text unsignedInteger
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->text('organization_name')->nullable();
            $table->text('project_county_district')->nullable();
            $table->text('description_of_project_timeline')->nullable();
            $table->text('siting_strategy_description')->nullable();
            $table->text('siting_strategy')->nullable();
            $table->text('land_tenure_project_area')->nullable();
            // $table->text('detailed_project_budget')->nullable();
            // $table->text('prof_of_land_tenure_mou')->nullable();
            $table->text('landholder_comm_engage')->nullable();
            $table->text('proj_partner_info')->nullable();
            $table->text('proj_success_risks')->nullable();
            $table->text('monitor_eval_plan')->nullable();
            $table->text('seedlings_source')->nullable();
            $table->tinyInteger('pct_employees_men')->nullable();
            $table->tinyInteger('pct_employees_women')->nullable();
            $table->tinyInteger('pct_employees_18to35')->nullable();
            $table->tinyInteger('pct_employees_older35')->nullable();
            $table->unsignedInteger('proj_beneficiaries')->nullable();
            $table->tinyInteger('pct_beneficiaries_women')->nullable();
            $table->tinyInteger('pct_beneficiaries_small')->nullable();
            $table->tinyInteger('pct_beneficiaries_large')->nullable();
            $table->tinyInteger('pct_beneficiaries_youth')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->dropColumn('organization_name')->nullable();
            $table->dropColumn('project_county_district')->nullable();
            $table->dropColumn('description_of_project_timeline')->nullable();
            $table->dropColumn('siting_strategy_description')->nullable();
            $table->dropColumn('siting_strategy')->nullable();
            $table->dropColumn('land_tenure_project_area')->nullable();
            // $table->dropColumn('detailed_project_budget')->nullable();
            // $table->dropColumn('prof_of_land_tenure_mou')->nullable();
            $table->dropColumn('landholder_comm_engage')->nullable();
            $table->dropColumn('proj_partner_info')->nullable();
            $table->dropColumn('proj_success_risks')->nullable();
            $table->dropColumn('monitor_eval_plan')->nullable();
            $table->dropColumn('seedlings_source')->nullable();
            $table->dropColumn('pct_employees_men')->nullable();
            $table->dropColumn('pct_employees_women')->nullable();
            $table->dropColumn('pct_employees_18to35')->nullable();
            $table->dropColumn('pct_employees_older35')->nullable();
            $table->dropColumn('proj_beneficiaries')->nullable();
            $table->dropColumn('pct_beneficiaries_women')->nullable();
            $table->dropColumn('pct_beneficiaries_small')->nullable();
            $table->dropColumn('pct_beneficiaries_large')->nullable();
            $table->dropColumn('pct_beneficiaries_youth')->nullable();
        });
    }
}