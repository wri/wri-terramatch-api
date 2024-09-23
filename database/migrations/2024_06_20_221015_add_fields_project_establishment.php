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
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->text('water_source')->nullable();
            $table->text('baseline_biodiversity')->nullable();
            $table->integer('goal_trees_restored_planting')->nullable();
            $table->integer('goal_trees_restored_anr')->nullable();
            $table->integer('goal_trees_restored_direct_seeding')->nullable();
        });
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->text('baseline_biodiversity')->nullable();
            $table->integer('goal_trees_restored_planting')->nullable();
            $table->integer('goal_trees_restored_anr')->nullable();
            $table->integer('goal_trees_restored_direct_seeding')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->dropColumn('water_source');
            $table->dropColumn('baseline_biodiversity');
            $table->dropColumn('goal_trees_restored_planting');
            $table->dropColumn('goal_trees_restored_anr');
            $table->dropColumn('goal_trees_restored_direct_seeding');
        });
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->dropColumn('baseline_biodiversity');
            $table->dropColumn('goal_trees_restored_planting');
            $table->dropColumn('goal_trees_restored_anr');
            $table->dropColumn('goal_trees_restored_direct_seeding');
        });
    }
};
