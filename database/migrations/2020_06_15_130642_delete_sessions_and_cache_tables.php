<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DeleteSessionsAndCacheTables extends Migration
{
    public function up()
    {
        Schema::drop("cache");
        Schema::drop("sessions");
    }

    public function down()
    {
    }
}
