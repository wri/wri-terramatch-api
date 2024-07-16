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
            $table->string('version_name')->nullable();
            $table->string('primary_uuid')->nullable()->after('uuid');
            $table->boolean('is_active')->default(false);
        });

        DB::statement('UPDATE site_polygon set primary_uuid = uuid;');

        Schema::table('site_polygon', function (Blueprint $table) {
            $table->uuid('primary_uuid')->nullable(false)->change();
            $table->index('primary_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->dropColumn('version_name');
            $table->dropColumn('primary_uuid');
            $table->dropColumn('is_active');
        });
    }
};
