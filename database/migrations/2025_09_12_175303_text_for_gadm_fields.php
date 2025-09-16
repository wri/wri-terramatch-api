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
        Schema::table('organisations', function (Blueprint $table) {
            $table->text('level_0_past_restoration')->nullable()->change();
            $table->text('level_1_past_restoration')->nullable()->change();
            $table->text('level_2_past_restoration')->nullable()->change();
        });
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->text('level_0_proposed')->nullable()->change();
            $table->text('level_1_proposed')->nullable()->change();
            $table->text('level_2_proposed')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->string('level_0_past_restoration')->nullable()->change();
            $table->string('level_1_past_restoration')->nullable()->change();
            $table->string('level_2_past_restoration')->nullable()->change();
        });
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->string('level_0_proposed')->nullable()->change();
            $table->string('level_1_proposed')->nullable()->change();
            $table->string('level_2_proposed')->nullable()->change();
        });
    }
};
