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
        Schema::dropIfExists('status');
        Schema::create('status', function (Blueprint $table) {
            $table->id();
            $table->string('entity')->nullable();
            $table->string('entity_uuid')->nullable();
            $table->string('status')->nullable();
            $table->string('comment')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->enum('type', ['change-request', 'status', 'submission', 'comment'])->nullable();
            $table->boolean('is_submitted')->nullable();
            $table->boolean('is_active')->nullable();
            $table->boolean('request_removed')->nullable();
            $table->date('date_created')->nullable();
            $table->string('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status');
    }
};
