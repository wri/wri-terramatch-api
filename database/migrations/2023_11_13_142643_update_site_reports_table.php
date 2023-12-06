<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSiteReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable()->change();
        });

        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->integer('pt_non_youth')->nullable()->after('pt_youth');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
