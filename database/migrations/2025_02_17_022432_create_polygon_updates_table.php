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
        Schema::create('polygon_updates', function (Blueprint $table) {
            $table->id();
            $table->uuid('site_polygon_uuid');
            $table->string('version_name')->nullable();
            $table->text('change')->nullable();
            $table->unsignedBigInteger('updated_by_id');
            $table->text('comment')->nullable();
            $table->string('type');
            $table->foreign('site_polygon_uuid')->references('uuid')->on('site_polygon');
            $table->foreign('updated_by_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polygon_updates');
    }
};
