<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
      Schema::table('site_polygon', function (Blueprint $table) {
          $table->index('poly_id', 'idx_site_polygon_poly_id');
      });
  }

  public function down(): void
  {
      Schema::table('site_polygon', function (Blueprint $table) {
          $table->dropIndex('idx_site_polygon_poly_id');
      });
  }
};
