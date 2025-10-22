<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('world_countries_generalized', function (Blueprint $table) {
            $table->index('iso', 'idx_world_countries_iso');
        });
    }

    public function down(): void
    {
        Schema::table('world_countries_generalized', function (Blueprint $table) {
            $table->dropIndex('idx_world_countries_iso');
        });
    }
};
