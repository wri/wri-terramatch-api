<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateInterventionsOnProjectPitchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->text('land_use_types')->after('restoration_intervention_types')->nullable();
            $table->text('restoration_strategy')->after('land_use_types')->nullable();
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
            $table->dropColumn('land_use_types', 'restoration_strategy');
        });
    }
}
