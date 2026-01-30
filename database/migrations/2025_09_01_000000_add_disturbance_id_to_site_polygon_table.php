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
            $table->unsignedBigInteger('disturbance_id')->nullable();
            $table->foreign('disturbance_id')->references('id')->on('v2_disturbances');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->dropForeign(['disturbance_id']);
            $table->dropColumn('disturbance_id');
        });
    }
};
