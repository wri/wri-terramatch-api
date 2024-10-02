<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTotalHectaresRestoredGoalTypeInV2Projects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->decimal('total_hectares_restored_goal', 15, 1)->change();
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
            $table->unsignedInteger('total_hectares_restored_goal')->change();
        });
    }
}
