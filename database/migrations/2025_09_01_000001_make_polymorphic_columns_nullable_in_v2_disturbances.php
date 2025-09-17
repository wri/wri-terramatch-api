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
        Schema::table('v2_disturbances', function (Blueprint $table) {
            $table->string('disturbanceable_type')->nullable()->change();
            $table->unsignedBigInteger('disturbanceable_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_disturbances', function (Blueprint $table) {
            $table->string('disturbanceable_type')->nullable(false)->change();
            $table->unsignedBigInteger('disturbanceable_id')->nullable(false)->change();
        });
    }
};
