<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateInterestsTable extends Migration
{
    public function up()
    {
        Schema::create("interests", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("organisation_id")->unsigned();
            $table->enum("initiator", ["offer", "pitch"]);
            $table->bigInteger("offer_id")->unsigned();
            $table->bigInteger("pitch_id")->unsigned();
            $table->boolean("matched")->default(false);
            $table->timestamps();
            $table->foreign("organisation_id")->references("id")->on("organisations");
            $table->foreign("offer_id")->references("id")->on("offers");
            $table->foreign("pitch_id")->references("id")->on("pitches");
        });
    }

    public function down()
    {
    }
}
