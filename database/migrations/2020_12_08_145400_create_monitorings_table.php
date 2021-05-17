<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoringsTable extends Migration
{
    public function up()
    {
        Schema::create("monitorings", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("match_id")->unique();
            $table->enum("initiator", ["offer", "pitch"]);
            $table->enum("stage", ["awaiting_targets", "negotiating_targets", "accepted_targets"]);
            $table->enum("negotiating", ["offer", "pitch"])->nullable();
            $table->unsignedBigInteger("created_by");
            $table->timestamps();
            $table->foreign("match_id")->references("id")->on("matches");
            $table->foreign("created_by")->references("id")->on("users");
        });
    }

    public function down()
    {
        Schema::dropIfExists("monitorings");
    }
}
