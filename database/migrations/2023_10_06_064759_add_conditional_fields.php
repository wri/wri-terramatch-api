<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConditionalFields extends Migration
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
                $table->json('answers')->before('feedback')->nullable();
            });
        }

        Schema::table('form_questions', function (Blueprint $table) {
            $table->boolean('show_on_parent_condition')->after('parent_id')->nullable();
        });
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
                $table->dropColumn('answers');
            });
        }

        Schema::table('form_questions', function (Blueprint $table) {
            $table->dropColumn('show_on_parent_condition');
        });
    }
}
