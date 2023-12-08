<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateWorkdayDisaggregationFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_workdays', function (Blueprint $table) {
            $table->renameColumn('role', 'collection');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_workdays', function (Blueprint $table) {
            $table->renameColumn('collection', 'role');
        });
    }
}
