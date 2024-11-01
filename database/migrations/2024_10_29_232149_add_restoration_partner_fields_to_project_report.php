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
            $table->unsignedInteger('total_unique_restoration_partners');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropColumn('total_unique_restoration_partners');
        });
    }
};
