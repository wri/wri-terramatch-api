<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToProjectReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->integer('seedlings_grown')->nullable();
            $table->dateTime('submitted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropColumn(['seedlings_grown', 'submitted_at']);
        });
    }
}
