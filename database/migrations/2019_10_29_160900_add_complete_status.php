<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddCompleteStatus extends Migration
{
    public function up()
    {
        Schema::table("offers", function (Blueprint $table) {
            $table->boolean("completed")->default(false);
            $table->boolean("successful")->nullable();
            $table->bigInteger("completed_by")->unsigned()->nullable();
            $table->dateTime("completed_at")->nullable();
            $table->foreign("completed_by")->references("id")->on("users");
        });
        Schema::table("pitches", function (Blueprint $table) {
            $table->boolean("completed")->default(false);
            $table->boolean("successful")->nullable();
            $table->bigInteger("completed_by")->unsigned()->nullable();
            $table->dateTime("completed_at")->nullable();
            $table->foreign("completed_by")->references("id")->on("users");
        });
    }

    public function down()
    {
    }
}
