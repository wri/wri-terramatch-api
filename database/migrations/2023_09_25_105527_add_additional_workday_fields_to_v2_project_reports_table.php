<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalWorkdayFieldsToV2ProjectReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->unsignedInteger('ethnic_indigenous_1')->nullable();
            $table->unsignedInteger('ethnic_indigenous_2')->nullable();
            $table->unsignedInteger('ethnic_indigenous_3')->nullable();
            $table->unsignedInteger('ethnic_indigenous_4')->nullable();
            $table->unsignedInteger('ethnic_indigenous_5')->nullable();
            $table->unsignedInteger('ethnic_other_1')->nullable();
            $table->unsignedInteger('ethnic_other_2')->nullable();
            $table->unsignedInteger('ethnic_other_3')->nullable();
            $table->unsignedInteger('ethnic_other_4')->nullable();
            $table->unsignedInteger('ethnic_other_5')->nullable();
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
            $table->dropColumn([
                'ethnic_indigenous_1',
                'ethnic_indigenous_2',
                'ethnic_indigenous_3',
                'ethnic_indigenous_4',
                'ethnic_indigenous_5',
                'ethnic_other_1',
                'ethnic_other_2',
                'ethnic_other_3',
                'ethnic_other_4',
                'ethnic_other_5',
            ]);
        });
    }
}
