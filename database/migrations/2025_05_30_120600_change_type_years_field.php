<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('financial_indicators', function (Blueprint $table) {
            $table->smallInteger('year')->unsigned()->change();
        });
    }

    public function down()
    {
        Schema::table('financial_indicators', function (Blueprint $table) {
            $table->tinyInteger('year')->unsigned()->change();
        });
    }
};
