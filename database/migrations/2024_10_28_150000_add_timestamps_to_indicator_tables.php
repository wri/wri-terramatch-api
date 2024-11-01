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
        Schema::table('code_indicator', function (Blueprint $table) {
            $table->timestamps();
            $table->dropColumn('date_created');
        });

        Schema::table('indicator_output_tree_cover_loss', function (Blueprint $table) {
            $table->timestamps();
            $table->dropColumn('date_created');
        });

        Schema::table('indicator_output_hectares', function (Blueprint $table) {
            $table->timestamps();
            $table->dropColumn('date_created');
        });

        Schema::table('indicator_output_tree_count', function (Blueprint $table) {
            $table->timestamps();
            $table->dropColumn('date_created');
        });

        Schema::table('indicator_output_tree_cover', function (Blueprint $table) {
            $table->timestamps();
            $table->dropColumn('date_created');
        });

        Schema::table('indicator_output_field_monitoring', function (Blueprint $table) {
            $table->timestamps();
            $table->dropColumn('date_created');
        });

        Schema::table('indicator_output_msu_carbon', function (Blueprint $table) {
            $table->timestamps();
            $table->dropColumn('date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('code_indicator', function (Blueprint $table) {
            $table->date('date_created')->nullable();
        });

        Schema::table('indicator_output_tree_cover_loss', function (Blueprint $table) {
            $table->date('date_created')->nullable();
        });

        Schema::table('indicator_output_hectares', function (Blueprint $table) {
            $table->date('date_created')->nullable();
        });

        Schema::table('indicator_output_tree_count', function (Blueprint $table) {
            $table->date('date_created')->nullable();
        });

        Schema::table('indicator_output_tree_cover', function (Blueprint $table) {
            $table->date('date_created')->nullable();
        });

        Schema::table('indicator_output_field_monitoring', function (Blueprint $table) {
            $table->date('date_created')->nullable();
        });

        Schema::table('indicator_output_msu_carbon', function (Blueprint $table) {
            $table->date('date_created')->nullable();
        });
    }
};
