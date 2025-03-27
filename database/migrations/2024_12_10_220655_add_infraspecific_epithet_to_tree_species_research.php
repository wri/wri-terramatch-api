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
        Schema::table('tree_species_research', function (Blueprint $table) {
            $table->string('infraspecific_epithet');
            $table->unique('scientific_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tree_species_research', function (Blueprint $table) {
            $table->dropColumn('infraspecific_epithet');
            $table->dropIndex('tree_species_research_scientific_name_unique');
        });
    }
};
