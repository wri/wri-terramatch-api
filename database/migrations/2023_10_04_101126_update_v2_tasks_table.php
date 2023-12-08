<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateV2TasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_tasks', function (Blueprint $table) {
            $table->dateTime('due_at')->after('period_key')->nullable();
        });

        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->integer('completion')->after('due_at')->default(0);
            $table->string('completion_status')->after('completion')->default('not-started');
        });

        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->integer('completion')->after('due_at')->default(0);
            $table->string('completion_status')->after('completion')->default('not-started');
        });

        Schema::table('v2_nursery_reports', function (Blueprint $table) {
            $table->integer('completion')->after('due_at')->default(0);
            $table->string('completion_status')->after('completion')->default('not-started');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_tasks', function (Blueprint $table) {
            $table->dropColumn('due_at');
        });
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropColumn('completion', 'completion_status');
        });
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->dropColumn('completion', 'completion_status');
        });
        Schema::table('v2_nursery_reports', function (Blueprint $table) {
            $table->dropColumn('completion', 'completion_status');
        });
    }
}
