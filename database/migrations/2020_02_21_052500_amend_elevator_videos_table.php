<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AmendElevatorVideosTable extends Migration
{
    public function up()
    {
        Schema::table('elevator_videos', function (Blueprint $table) {
            $table->dropColumn("concatenated");
            $table->bigInteger("upload_id")->unsigned()->nullable();
            $table->foreign("upload_id")->references("id")->on("uploads");
        });
    }

    public function down()
    {
    }
}
