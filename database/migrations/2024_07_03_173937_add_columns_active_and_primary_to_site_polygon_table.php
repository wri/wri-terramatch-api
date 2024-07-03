<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            if (!Schema::hasColumn('site_polygon', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            if (!Schema::hasColumn('site_polygon', 'primary_uuid')) {
                $table->char('primary_uuid', 36)->nullable();
            }
        });

        // Now that the column exists, we can update it
        DB::statement('UPDATE site_polygon SET primary_uuid = uuid WHERE primary_uuid IS NULL');

        // After updating, we can set it to not nullable
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->char('primary_uuid', 36)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            if (Schema::hasColumn('site_polygon', 'is_active')) {
                $table->dropColumn('is_active');
            }
            
            if (Schema::hasColumn('site_polygon', 'primary_uuid')) {
                $table->dropColumn('primary_uuid');
            }
        });
    }
};
