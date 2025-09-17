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
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->text('goal_trees_restored_description')->nullable();
            $table->text('jobs_created_beneficiaries_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            Schema::table('project_pitches', function (Blueprint $table) {
                $table->dropColumn('goal_trees_restored_description');
                $table->dropColumn('jobs_created_beneficiaries_description');
            });
        });
    }
};
