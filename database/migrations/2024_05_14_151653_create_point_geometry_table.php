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
        Schema::dropIfExists('point_geometry');
        Schema::create('point_geometry', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->geometry('geom')->nullable();
            $table->decimal('est_area', 15, 2)->nullable();
            $table->string('created_by')->nullable();
            $table->string('last_modified_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_geometry');
    }
};
