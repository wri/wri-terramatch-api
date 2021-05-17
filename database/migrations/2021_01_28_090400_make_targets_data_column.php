<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class MakeTargetsDataColumn extends Migration
{
    public function up()
    {
        Schema::table("targets", function (Blueprint $table) {
            $table->json("data");
        });
    }

    public function down()
    {
    }
}
