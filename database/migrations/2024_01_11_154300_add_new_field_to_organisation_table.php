<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldToOrganisationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->tinyInteger('total_employees')->nullable();
            $table->text('socioeconomic_impact')->nullable();
            $table->text('environmental_impact')->nullable();
            $table->text('growith_stage')->nullable();
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
                'total_employees',
                'socioeconomic_impact',
                'environmental_impact',
                'growith_stage',
            ]);
        });
    }
}
