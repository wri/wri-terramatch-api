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
        Schema::table('srp_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable();
            $table->integer('completion')->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('srp_reports', function (Blueprint $table) {
            $table->dropColumn('task_id');
            $table->integer('completion')->nullable(false)->default(0)->change();
        });
    }
};
