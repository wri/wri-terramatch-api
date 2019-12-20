<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreatePasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop("password_resets");
        Schema::create("password_resets", function (Blueprint $table) {
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
