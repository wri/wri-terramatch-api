<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update only the 'iso' column to VARCHAR(3)
        DB::statement('ALTER TABLE world_countries_generalized MODIFY COLUMN iso VARCHAR(3)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'iso' column back to VARCHAR(2)
        DB::statement('ALTER TABLE world_countries_generalized MODIFY COLUMN iso VARCHAR(2)');
    }
};
