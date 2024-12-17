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
        Schema::table('delayed_jobs', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id'); // Adjust 'after' based on the desired column position
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delayed_jobs', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
