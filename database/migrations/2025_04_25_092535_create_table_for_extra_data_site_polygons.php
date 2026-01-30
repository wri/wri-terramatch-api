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
        Schema::create('site_polygon_data', function (Blueprint $table) {
            $table->id();
            $table->string('site_polygon_uuid');
            $table->json('data');
            $table->timestamps();
            $table->foreign('site_polygon_uuid')->references('uuid')->on('site_polygon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_polygon_data');
    }
};
