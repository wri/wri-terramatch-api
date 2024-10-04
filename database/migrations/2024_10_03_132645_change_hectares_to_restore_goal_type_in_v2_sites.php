<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeHectaresToRestoreGoalTypeInV2Sites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->decimal('hectares_to_restore_goal', 15, 1)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->unsignedInteger('hectares_to_restore_goal')->change();
        });
    }
}
