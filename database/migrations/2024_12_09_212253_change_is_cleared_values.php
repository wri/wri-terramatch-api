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
        Schema::table('delayed_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('delayed_jobs', 'is_cleared')) {
                $table->dropColumn('is_cleared');
            }
            if (!Schema::hasColumn('delayed_jobs', 'is_acknowledged')) {
                $table->boolean('is_acknowledged')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delayed_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('delayed_jobs', 'is_acknowledged')) {
                $table->dropColumn('is_acknowledged');
            }
            if (!Schema::hasColumn('delayed_jobs', 'is_cleared')) {
                $table->boolean('is_cleared')->default(false);
            }
        });
    }
};
