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
        Schema::table('funding_programmes', function (Blueprint $table) {
            $table->integer('location_id')->nullable()->after('location')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funding_programmes', function (Blueprint $table) {
            $table->string('location_id')->nullable()->change();
        });
    }
};
