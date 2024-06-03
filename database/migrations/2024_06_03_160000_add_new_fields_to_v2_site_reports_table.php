<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->integer('num_trees_regenerating')->nullable();
            $table->string('regenerating_description')->nullable();
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
            $table->dropColumn(['num_trees_regenerating', 'regenerating_description']);
        });
    }
};
