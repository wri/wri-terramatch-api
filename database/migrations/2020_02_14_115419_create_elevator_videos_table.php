<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElevatorVideosTable extends Migration
{
    public function up()
    {
        Schema::create('elevator_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("user_id")->unsigned();
            $table->enum("status", ["processing", "finished", "errored", "timed_out"])->default("processing");
            $table->string('introduction')->nullable();
            $table->string('aims')->nullable();
            $table->string('importance')->nullable();
            $table->string("job_id")->nullable();
            $table->string("concatenated")->nullable();
            $table->timestamps();
            $table->foreign("user_id")->references("id")->on("users");
        });
    }

    public function down()
    {
    }
}
