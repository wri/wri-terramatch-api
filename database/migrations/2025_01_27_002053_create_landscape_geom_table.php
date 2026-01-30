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
        if (Schema::hasTable('landscape_geom')) {
            // assume that if the table already exists in this env we don't want to recreate it.
            return;
        }
        Schema::create('landscape_geom', function (Blueprint $table) {
            $table->id();
            $table->geometry('geometry')->spatialIndex('geometry');
            $table->string('landscape', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landscape_geom');
    }
};
