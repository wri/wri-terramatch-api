<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndiaFieldsToProjectPitchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->text('states')->nullable();
            $table->integer('hectares_first_yr')->nullable();
            $table->integer('total_trees_first_yr')->nullable();
            $table->integer('pct_beneficiaries_backward_class')->nullable();
            $table->text('land_systems')->nullable();
            $table->text('tree_restoration_practices')->nullable();
            $table->text('detailed_intervention_types')->nullable();
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
                'states',
                'hectares_first_yr',
                'total_trees_first_yr',
                'pct_beneficiaries_backward_class',
            ]);
        });
    }
}
