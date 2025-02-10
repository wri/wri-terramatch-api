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
      Schema::create('impact_stories', function (Blueprint $table) {
        $table->id();
        $table->string('title', 71);
        $table->string('status');
        $table->unsignedBigInteger('organization_id');
        $table->date('date');
        $table->json('category');
        $table->string('thumbnail');
        $table->longText('content');
        $table->string('sm_instagram')->nullable();
        $table->string('sm_x')->nullable();
        $table->string('sm_facebook')->nullable();
        $table->string('sm_linkedin')->nullable();
        $table->timestamps();
        
        $table->foreign('organization_id')->references('id')->on('organisations')->onDelete('cascade');
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
