<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->string('anr_practices')->nullable();
        });
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->tinyInteger('nursery_seedlings_goal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->dropColumn('anr_practices');
        });
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->dropColumn('nursery_seedlings_goal');
        });
    }
};
