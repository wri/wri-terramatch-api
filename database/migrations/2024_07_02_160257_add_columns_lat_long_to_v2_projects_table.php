<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('v2_projects', function (Blueprint $table) {
            if (! Schema::hasColumn('v2_projects', 'lat')) {
                $table->decimal('lat', 10, 8)->nullable();
            }
            if (! Schema::hasColumn('v2_projects', 'long')) {
                $table->decimal('long', 11, 8)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('v2_projects', function (Blueprint $table) {
            if (Schema::hasColumn('v2_projects', 'lat')) {
                $table->dropColumn('lat');
            }
            if (Schema::hasColumn('v2_projects', 'long')) {
                $table->dropColumn('long');
            }
        });
    }
};
