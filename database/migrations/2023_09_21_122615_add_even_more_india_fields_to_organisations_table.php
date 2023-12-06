<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvenMoreIndiaFieldsToOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->text('field_staff_skills')->nullable();
            $table->enum('fpc_company', ['yes' ,'no'])->nullable();
            $table->integer('num_of_farmers_on_board')->nullable();
            $table->integer('num_of_marginalised_employees')->nullable();
            $table->text('benefactors_fpc_company')->nullable();
            $table->string('board_remuneration_fpc_company')->nullable();
            $table->string('board_engagement_fpc_company')->nullable();
            $table->string('biodiversity_focus')->nullable();
            $table->text('global_planning_frameworks')->nullable();
            $table->text('past_gov_collaboration')->nullable();
            $table->text('engagement_landless')->nullable();
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
                'field_staff_skills',
                'fpc_company',
                'num_of_farmers_on_board',
                'num_of_marginalised_employees',
                'benefactors_fpc_company',
                'board_remuneration_fpc_company',
                'board_engagement_fpc_company',
                'biodiversity_focus',
                'global_planning_frameworks',
                'past_gov_collaboration',
                'engagement_landless',
            ]);
        });
    }
}
