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
        //
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->decimal('lat_proposed', 15, 8)->nullable()->change();
            $table->decimal('long_proposed', 15, 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->decimal('lat_proposed', 10, 8)->nullable()->change();
            $table->decimal('long_proposed', 10, 8)->nullable()->change();
        });
    }
};
