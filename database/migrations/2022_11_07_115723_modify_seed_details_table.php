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
        Schema::table('seed_details', function (Blueprint $table) {
            $table->integer('seeds_in_sample')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('seed_details', function (Blueprint $table) {
            $table->dropColumn('seeds_in_sample');
        });
    }
};
