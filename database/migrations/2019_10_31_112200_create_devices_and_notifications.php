<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateDevicesAndNotifications extends Migration
{
    public function up()
    {
        Schema::create("devices", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("user_id")->unsigned();
            $table->enum("os", ["ios", "android"]);
            $table->string("uuid");
            $table->string("push_token");
            $table->longText("arn");
            $table->timestamps();
            $table->foreign("user_id")->references("id")->on("users");
        });
        Schema::create("notifications", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("user_id")->unsigned();
            $table->string("title");
            $table->longText("body");
            $table->boolean("unread")->default(true);
            $table->timestamps();
            $table->foreign("user_id")->references("id")->on("users");
        });
    }

    public function down()
    {
    }
}
