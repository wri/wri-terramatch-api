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
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->enum('planting_status', [
                'no-restoration-expected',
                'not-started',
                'in-progress',
                'replacement-planting',
                'completed',
            ])->nullable()->after('status');
        });

        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->enum('planting_status', [
                'no-restoration-expected',
                'not-started',
                'in-progress',
                'replacement-planting',
                'completed',
            ])->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->dropColumn('planting_status');
        });

        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropColumn('planting_status');
        });
    }
};
