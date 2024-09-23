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
        Schema::table('terrafund_programme_submissions', function (Blueprint $table) {
            $table->text('landscape_community_contribution')->nullable();
            $table->text('top_three_successes')->nullable();
            $table->text('challenges_and_lessons')->nullable();
            $table->text('maintenance_and_monitoring_activities')->nullable();
            $table->text('significant_change')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('terrafund_programme_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'landscape_community_contribution',
                'top_three_successes',
                'challenges_and_lessons',
                'maintenance_and_monitoring_activities',
                'significant_change',
            ]);
        });
    }
};
