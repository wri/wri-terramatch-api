<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePlantendFromSitePolygonTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->dropColumn('plantend');
        });

        Schema::table('site_polygon_local', function (Blueprint $table) {
            $table->dropColumn('plantend');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->date('plantend')->nullable()->after('plantstart');
        });

        Schema::table('site_polygon_local', function (Blueprint $table) {
            $table->date('plantend')->nullable()->after('plantstart');
        });
    }
} 