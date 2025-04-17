<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsValidToSitePolygonTable extends Migration
{
    public function up(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->string('validation_status')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->dropColumn('validation_status');
        });
    }
}
