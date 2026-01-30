<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToSitePolygonTable extends Migration
{
    public function up(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->index('site_id', 'idx_site_polygon_site_id');
            $table->index('status', 'idx_site_polygon_status');
        });
    }

    public function down(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->dropIndex('idx_site_polygon_site_id');
            $table->dropIndex('idx_site_polygon_status');
        });
    }
}
