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
        Schema::table('terrafund_nursery_submissions', function (Blueprint $table) {
            $table->string('shared_drive_link', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('terrafund_nursery_submissions', function (Blueprint $table) {
            $table->string('shared_drive_link', 255)->nullable();
        });
    }
};
