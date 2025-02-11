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
      Schema::create('impact_stories', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->string('title', 71);
        $table->string('status');
        $table->unsignedBigInteger('organization_id');
        $table->date('date');
        $table->json('category');
        $table->string('thumbnail');
        $table->json('content');
        $table->timestamps();
        $table->softDeletes();
      });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impact_stories');
    }
};
