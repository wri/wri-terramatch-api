<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSatelliteMapsTable extends Migration
{
    public function up()
    {
        Schema::create("satellite_maps", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("monitoring_id");
            $table->string("map");
            $table->string("alt_text");
            $table->timestamps();
            $table->unsignedBigInteger("created_by");
            $table->foreign("created_by")->references("id")->on("users");
            $table->foreign("monitoring_id")->references("id")->on("monitorings");
        });
    }

    public function down()
    {
    }
}
