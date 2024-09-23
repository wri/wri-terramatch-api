<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('seed_details', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }

    public function down()
    {
        Schema::table('seed_details', function (Blueprint $table) {
            $table->integer('amount');
        });
    }
};
