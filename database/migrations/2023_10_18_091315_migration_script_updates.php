<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrationScriptUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_invasives', function (Blueprint $table) {
            $table->string('old_model')->after('collection')->nullable();
            $table->integer('old_id')->after('old_model')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_invasives', function (Blueprint $table) {
            $table->dropColumn('old_model', 'old_id');
        });
    }
}
