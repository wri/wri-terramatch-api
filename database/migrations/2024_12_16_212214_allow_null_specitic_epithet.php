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
        Schema::table('tree_species_research', function (Blueprint $table) {
            $table->string('specific_epithet')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tree_species_research', function (Blueprint $table) {
            $table->string('specific_epithet')->nullable(false)->change();
        });
    }
};
