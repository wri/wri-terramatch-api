<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RemoveNotifications extends Migration
{
    public function up()
    {
        Schema::drop("notifications");
    }

    public function down()
    {
    }
}
