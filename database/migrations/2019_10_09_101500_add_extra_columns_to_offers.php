<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddExtraColumnsToOffers extends Migration
{
    public function up()
    {
        Schema::table("offers", function (Blueprint $table) {
            $table->string("cover_photo")->nullable();
            $table->string("avatar")->nullable();
            $table->string("video")->nullable();
        });
    }

    public function down()
    {
    }
}
