<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreIndiaFieldsToProjectPitchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->renameColumn('pct_beneficiaries_35below', 'pct_beneficiaries_youth');
            $table->text('monitoring_evaluation_plan')->nullable();
            $table->integer('pct_beneficiaries_scheduled_classes')->nullable();
            $table->integer('pct_beneficiaries_scheduled_tribes')->nullable();
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
            $table->renameColumn('pct_beneficiaries_youth', 'pct_beneficiaries_35below');
            $table->dropColumn([
                'monitoring_evaluation_plan',
                'pct_beneficiaries_scheduled_classes',
                'pct_beneficiaries_scheduled_tribes',
            ]);
        });
    }
}
