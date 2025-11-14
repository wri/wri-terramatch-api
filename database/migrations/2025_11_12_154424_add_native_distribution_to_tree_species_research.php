<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tree_species_research', function (Blueprint $table) {
            $table->text('native_distribution')->nullable();
            $table->text('suitability')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tree_species_research', function (Blueprint $table) {
            $table->dropColumn('native_distribution');
            $table->dropColumn('suitability');
        });
    }
};
