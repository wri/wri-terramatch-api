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
        // Create investments table
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained('v2_projects')->onDelete('cascade');
            $table->date('investment_date');
            $table->string('type');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'investment_date']);
            $table->index('type');
        });

        // Create investment_splits table
        Schema::create('investment_splits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('investment_id')->constrained('investments')->onDelete('cascade');
            $table->string('funder');
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            $table->softDeletes();

            $table->index('investment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_splits');
        Schema::dropIfExists('investments');
    }
};
