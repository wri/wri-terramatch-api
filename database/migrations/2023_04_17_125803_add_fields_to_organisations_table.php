<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->unsignedInteger('ft_permanent_employees')->nullable();
            $table->unsignedInteger('pt_permanent_employees')->nullable();
            $table->unsignedInteger('temp_employees')->nullable();
            $table->unsignedInteger('female_employees')->nullable();
            $table->unsignedInteger('male_employees')->nullable();
            $table->unsignedInteger('young_employees')->nullable();
            $table->text('additional_funding_details')->nullable();
            $table->text('community_experience')->nullable();
            $table->unsignedInteger('total_engaged_community_members_3yr')->nullable();
            $table->tinyInteger('percent_engaged_women_3yr')->nullable();
            $table->tinyInteger('percent_engaged_men_3yr')->nullable();
            $table->tinyInteger('percent_engaged_under_35_3yr')->nullable();
            $table->tinyInteger('percent_engaged_over_35_3yr')->nullable();
            $table->tinyInteger('percent_engaged_smallholder_3yr')->nullable();
            $table->unsignedInteger('total_trees_grown')->nullable();
            $table->tinyInteger('avg_tree_survival_rate')->nullable();
            $table->text('tree_maintenance_aftercare_approach')->nullable();
            $table->text('restored_areas_description')->nullable();
            $table->text('monitoring_evaluation_experience')->nullable();
            $table->longText('funding_history')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn([
                'ft_permanent_employees',
                'pt_permanent_employees',
                'temp_employees',
                'female_employees',
                'male_employees',
                'young_employees',
                'additional_funding_details',
                'community_experience',
                'total_engaged_community_members_3yr',
                'percent_engaged_women_3yr',
                'percent_engaged_men_3yr',
                'percent_engaged_under_35_3yr',
                'percent_engaged_over_35_3yr',
                'percent_engaged_smallholder_3yr',
                'total_trees_grown',
                'avg_tree_survival_rate',
                'tree_maintenance_aftercare_approach',
                'restored_areas_description',
                'monitoring_evaluation_experience',
                'funding_history',
            ]);
        });
    }
}
