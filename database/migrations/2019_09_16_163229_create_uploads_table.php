<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUploadsTable extends Migration
{
    public function up()
    {
        Schema::create("uploads", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("user_id")->unsigned();
            $table->string("location");
            $table->timestamps();
            $table->foreign("user_id")->references("id")->on("users");
        });
    }

    public function down()
    {
    }
}
