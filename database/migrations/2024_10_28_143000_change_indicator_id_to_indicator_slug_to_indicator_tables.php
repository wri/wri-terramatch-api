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
        Schema::table('indicator_output_tree_cover_loss', function (Blueprint $table) {
            $table->string('indicator_slug')->nullable();
            $table->dropColumn('indicator_id');
        });

        Schema::table('indicator_output_hectares', function (Blueprint $table) {
            $table->string('indicator_slug')->nullable();
            $table->dropColumn('indicator_id');
        });

        Schema::table('indicator_output_tree_count', function (Blueprint $table) {
            $table->string('indicator_slug')->nullable();
            $table->dropColumn('indicator_id');
        });

        Schema::table('indicator_output_tree_cover', function (Blueprint $table) {
            $table->string('indicator_slug')->nullable();
            $table->dropColumn('indicator_id');
        });

        Schema::table('indicator_output_field_monitoring', function (Blueprint $table) {
            $table->string('indicator_slug')->nullable();
            $table->dropColumn('indicator_id');
        });

        Schema::table('indicator_output_msu_carbon', function (Blueprint $table) {
            $table->string('indicator_slug')->nullable();
            $table->dropColumn('indicator_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indicator_output_tree_cover_loss', function (Blueprint $table) {
            $table->dropColumn('indicator_slug');
            $table->enum('indicator_id', [2, 3])->nullable();
        });

        Schema::table('indicator_output_hectares', function (Blueprint $table) {
            $table->dropColumn('indicator_slug');
            $table->enum('indicator_id', [4, 5, 6])->nullable();
        });

        Schema::table('indicator_output_tree_count', function (Blueprint $table) {
            $table->dropColumn('indicator_slug');
            $table->enum('indicator_id', [7, 8])->nullable();
        });

        Schema::table('indicator_output_tree_cover', function (Blueprint $table) {
            $table->dropColumn('indicator_slug');
            $table->enum('indicator_id', [1])->nullable();
        });

        Schema::table('indicator_output_field_monitoring', function (Blueprint $table) {
            $table->dropColumn('indicator_slug');
            $table->enum('indicator_id', [9])->nullable();
        });

        Schema::table('indicator_output_msu_carbon', function (Blueprint $table) {
            $table->dropColumn('indicator_slug');
            $table->enum('indicator_id', [10])->nullable();
        });
    }
};
