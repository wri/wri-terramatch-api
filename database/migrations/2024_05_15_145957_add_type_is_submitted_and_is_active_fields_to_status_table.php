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
        Schema::table('status', function (Blueprint $table) {
            $table->enum('type', ['change-request', 'status', 'submission', 'comment'])->nullable();
            $table->boolean('is_submitted')->nullable();
            $table->boolean('is_active')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('is_submitted');
            $table->dropColumn('is_active');
        });
    }
};
