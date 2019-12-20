<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ChangeLandToFloat extends Migration
{
    public function up()
    {
        Schema::table("offers", function (Blueprint $table) {
            $table->float("land_size")->change();
        });
    }

    public function down()
    {
    }
}
