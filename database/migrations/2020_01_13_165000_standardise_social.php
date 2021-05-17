<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StandardiseSocial extends Migration
{
    public function  up()
    {
        Schema::table("pitch_versions", function (Blueprint $table) {
            $table->string("linkedin")->nullable();
        });
    }

    public function down()
    {
    }
}
