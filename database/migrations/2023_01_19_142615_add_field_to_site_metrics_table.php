<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('baseline_monitoring_metrics_site', function (Blueprint $table) {
            $table->decimal('field_tree_count', 10, 4)->nullable()->after('tree_cover');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('baseline_monitoring_metrics_site', function (Blueprint $table) {
            $table->dropColumn('field_tree_count');
        });
    }
};
