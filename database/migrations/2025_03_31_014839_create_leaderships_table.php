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
        Schema::create('leaderships', function (Blueprint $table) {
            $table->id(); // BIGINT auto-incremental
            $table->uuid('uuid')->unique();
            $table->foreignId('organisation_id')->constrained('organisations')->onDelete('cascade');
            $table->string('collection');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('position')->nullable();
            $table->string('gender')->nullable();
            $table->tinyInteger('age')->unsigned()->nullable();
            $table->string('nationality')->nullable();
            $table->softDeletes(); // deleted_at
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderships');
    }
};
