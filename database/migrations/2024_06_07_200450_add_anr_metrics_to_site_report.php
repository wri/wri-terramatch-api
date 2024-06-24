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
            $table->integer('num_trees_regenerating')->nullable();
            $table->text('regeneration_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->dropColumn('num_trees_regenerating');
            $table->dropColumn('regeneration_description');
        });
    }
};
