<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->float('a_nat_regeneration')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->unsignedInteger('a_nat_regeneration')->change();
        });
    }
};
