<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdxDelayedJobsUserStatus extends Migration
{
    public function up()
    {
        Schema::table('delayed_jobs', function (Blueprint $table) {
            $table->index(['created_by', 'is_acknowledged'], 'idx_delayed_jobs_user_status');
        });
    }

    public function down()
    {
        Schema::table('delayed_jobs', function (Blueprint $table) {
            $table->dropIndex('idx_delayed_jobs_user_status');
        });
    }
}
