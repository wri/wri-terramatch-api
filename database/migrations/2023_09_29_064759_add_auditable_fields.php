<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditableFields extends Migration
{
    private $tables = [
        'v2_sites',
        'v2_site_reports',
        'v2_projects',
        'v2_project_reports',
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
        foreach ($this->tables as $entityTable) {
            Schema::table($entityTable, function (Blueprint $table) {
                $table->text('feedback')->after('status')->nullable();
                $table->text('feedback_fields')->after('feedback')->nullable();
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
        foreach ($this->tables as $entityTable) {
            Schema::table($entityTable, function (Blueprint $table) {
                $table->dropColumn('feedback');
                $table->dropColumn('feedback_fields');
            });
        }
    }
}
