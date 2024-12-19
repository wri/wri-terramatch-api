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
        Schema::create('tree_species_research', function (Blueprint $table) {
            // This table intentionally avoids having an auto increment int ID PK.
            $table->string('taxon_id')->primary();

            $table->string('scientific_name');
            $table->string('family');
            $table->string('genus');
            $table->string('specific_epithet');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree_species_research');
    }
};
