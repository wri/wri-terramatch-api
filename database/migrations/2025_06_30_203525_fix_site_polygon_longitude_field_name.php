<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->decimal('long', 20, 16)->nullable()->after('lat');
        });

        // Copy data from lng to long
        DB::statement('UPDATE site_polygon SET `long` = lng WHERE lng IS NOT NULL');

        Schema::table('site_polygon', function (Blueprint $table) {
            $table->dropColumn('lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->decimal('lng', 20, 16)->nullable()->after('lat');
        });

        DB::statement('UPDATE site_polygon SET lng = `long` WHERE `long` IS NOT NULL');

        Schema::table('site_polygon', function (Blueprint $table) {
            $table->dropColumn('long');
        });
    }
};
