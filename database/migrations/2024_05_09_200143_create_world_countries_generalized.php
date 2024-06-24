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
        if (Schema::hasTable('world_countries_generalized')) {
            // assume that if the table already exists in this env we don't want to recreate it.
            return;
        }

        Schema::create('world_countries_generalized', function (Blueprint $table) {
            $table->integer('OGR_FID')->autoIncrement();
            $table->geometry('geometry')->spatialIndex('geometry');
            $table->string('country', 50)->nullable();
            $table->string('iso', 2)->nullable();
            $table->string('countryaff', 50)->nullable();
            $table->string('aff_iso', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_countries_generalized');
    }
};
