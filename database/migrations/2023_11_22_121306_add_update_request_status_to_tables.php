<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdateRequestStatusToTables extends Migration
{
    private $tableList = [
        'v2_projects',
        'v2_project_reports',
        'v2_sites',
        'v2_site_reports',
        'v2_nurseries',
        'v2_nursery_reports',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tableList as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('update_request_status')
                    ->after('status')
                    ->default('no-update')
                    ->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tableList as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('update_request_status');
            });
        }
    }
}
