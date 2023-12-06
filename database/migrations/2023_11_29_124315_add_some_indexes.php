<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->index('project_id');
        });

        Schema::table('v2_sites', function (Blueprint $table) {
            $table->index('project_id');
        });

        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->index('site_id');
        });

        Schema::table('v2_nurseries', function (Blueprint $table) {
            $table->index('project_id');
        });

        Schema::table('v2_nursery_reports', function (Blueprint $table) {
            $table->index('nursery_id');
        });

        Schema::table('v2_tree_species', function (Blueprint $table) {
            $table->index('collection');
            $table->index(['collection','speciesable_type', 'speciesable_id'], 'tree_species_type_id_collection');
        });

        Schema::table('v2_invasives', function (Blueprint $table) {
            $table->index('uuid');
            $table->index('collection');
            $table->index(['collection','invasiveable_type', 'invasiveable_id'], 'invasive_type_id_collection');
        });

        Schema::table('v2_disturbances', function (Blueprint $table) {
            $table->index('collection');
            $table->index(['collection','disturbanceable_type', 'disturbanceable_id'], 'disturbance_type_id_collection');
        });

        Schema::table('v2_tasks', function (Blueprint $table) {
            $table->index('uuid');
        });

        Schema::table('v2_actions', function (Blueprint $table) {
            $table->index('uuid');
            $table->index('organisation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
