<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateMatchesTable extends Migration
{
    public function up()
    {
        Schema::create("matches", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("primary_interest_id")->unsigned();
            $table->bigInteger("secondary_interest_id")->unsigned();
            $table->timestamps();
            $table->foreign("primary_interest_id")->references("id")->on("interests");
            $table->foreign("secondary_interest_id")->references("id")->on("interests");
        });
    }

    public function down()
    {
    }
}
