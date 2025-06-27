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
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->decimal('lat', 20, 16)->nullable()->after('status');
            $table->decimal('lng', 20, 16)->nullable()->after('lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }
};
