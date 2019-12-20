<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddArchivedFieldsToPitchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pitches', function (Blueprint $table) {
            $table->boolean("archived")->default(false);
            $table->bigInteger("archived_by")->unsigned()->nullable();
            $table->dateTime("archived_at")->nullable();

            $table->foreign("archived_by")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pitches', function (Blueprint $table) {
            $table->dropColumn(['archived', 'archived_by', 'archived_at']);
        });
    }
}
