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
        Schema::table('v2_workdays', function (Blueprint $table) {
            $table->boolean('migrated_to_demographics')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_workdays', function (Blueprint $table) {
            $table->dropColumn('migrated_to_demographics');
        });
    }
};
