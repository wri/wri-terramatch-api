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
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->unsignedInteger('direct_seeding_survival_rate')->nullable();
        });
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->unsignedInteger('direct_seeding_survival_rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->dropColumn('direct_seeding_survival_rate');
        });
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->dropColumn('direct_seeding_survival_rate');
        });
    }
};
