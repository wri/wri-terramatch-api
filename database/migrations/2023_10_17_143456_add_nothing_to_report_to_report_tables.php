<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNothingToReportToReportTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->boolean('nothing_to_report')->after('status')->nullable();
        });

        Schema::table('v2_nursery_reports', function (Blueprint $table) {
            $table->boolean('nothing_to_report')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->dropColumn('nothing_to_report');
        });

        Schema::table('v2_nursery_reports', function (Blueprint $table) {
            $table->dropColumn('nothing_to_report');
        });
    }
}
