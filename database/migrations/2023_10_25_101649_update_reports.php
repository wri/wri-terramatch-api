<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->string('soil_condition')->change();
        });

        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->dateTime('submitted_at')->nullable()->after('due_at');
            $table->text('disturbance_details')->nullable()->after('public_narrative');
            $table->bigInteger('seeds_planted')->nullable()->after('submitted_at');
        });

        Schema::table('v2_nursery_reports', function (Blueprint $table) {
            $table->dateTime('submitted_at')->nullable()->after('due_at');
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
            $table->dropColumn('submitted_at', 'disturbance_details', 'seeds_planted');
        });

        Schema::table('v2_nursery_reports', function (Blueprint $table) {
            $table->dropColumn('submitted_at');
        });
    }
}
