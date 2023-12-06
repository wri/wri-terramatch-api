<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatesForDataMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_project_monitorings', function (Blueprint $table) {
            $table->string('old_model')->nullable()->after('last_updated');
        });
        Schema::table('v2_site_monitorings', function (Blueprint $table) {
            $table->string('old_model')->nullable()->after('last_updated');
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
