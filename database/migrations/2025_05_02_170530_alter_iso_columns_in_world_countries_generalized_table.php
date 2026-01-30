<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE world_countries_generalized MODIFY COLUMN iso VARCHAR(3)');

        DB::statement('ALTER TABLE world_countries_generalized CHANGE COLUMN aff_iso alpha_2_iso VARCHAR(2)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE world_countries_generalized CHANGE COLUMN alpha_2_iso aff_iso VARCHAR(2)');

        DB::statement('ALTER TABLE world_countries_generalized MODIFY COLUMN iso VARCHAR(2)');
    }
};
