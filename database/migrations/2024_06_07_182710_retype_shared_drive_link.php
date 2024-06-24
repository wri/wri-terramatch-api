<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->text('shared_drive_link')->nullable()->change();
        });
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->text('shared_drive_link')->nullable()->change();
        });
        Schema::table('v2_nursery_reports', function (Blueprint $table) {
            $table->text('shared_drive_link')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->string('shared_drive_link')->nullable()->change();
        });
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->string('shared_drive_link')->nullable()->change();
        });
        Schema::table('v2_nursery_reports', function (Blueprint $table) {
            $table->string('shared_drive_link')->nullable()->change();
        });
    }
};
