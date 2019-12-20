<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerificationsTable extends Migration
{
    public function up()
    {
        Schema::create("verifications", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("token");
            $table->bigInteger("user_id")->unsigned();
            $table->timestamps();
            $table->foreign("user_id")->references("id")->on("users");
        });
    }

    public function down()
    {
    }
}
