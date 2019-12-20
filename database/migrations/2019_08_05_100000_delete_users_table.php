<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteUsersTable extends Migration
{
    public function up()
    {
        Schema::drop("users");
    }

    public function down()
    {
    }
}
