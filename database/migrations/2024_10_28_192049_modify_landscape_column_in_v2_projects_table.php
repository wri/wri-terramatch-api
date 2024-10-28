<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyLandscapeColumnInV2ProjectsTable extends Migration
{
    public function up(): void
    {
      if (Schema::hasColumn('v2_projects', 'landscape')) {
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->dropColumn('landscape');
        });
      }

      Schema::table('v2_projects', function (Blueprint $table) {
          $table->string('landscape')->nullable();
      });
    }

    public function down(): void
    {
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->dropColumn('landscape');
        });
    }
}
