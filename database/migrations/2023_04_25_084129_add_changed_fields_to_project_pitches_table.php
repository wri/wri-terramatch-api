<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangedFieldsToProjectPitchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->text('main_causes_of_degradation')->nullable();
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
            $table->dropColumn('main_causes_of_degradation');
        });
    }
}
