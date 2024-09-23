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
        Schema::table('terrafund_programmes', function (Blueprint $table) {
            $table->integer('trees_planted')->nullable();
            $table->integer('jobs_created')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('terrafund_programmes', function (Blueprint $table) {
            $table->dropColumn([
                'trees_planted',
                'jobs_created',
            ]);
        });
    }
};
