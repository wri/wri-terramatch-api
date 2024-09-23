<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('terrafund_programmes', function (Blueprint $table) {
            $table->boolean('skip_submission_cycle')->default(false);
        });
    }

    public function down()
    {
        Schema::table('terrafund_programmes', function (Blueprint $table) {
            $table->dropColumn('skip_submission_cycle');
        });
    }
};
