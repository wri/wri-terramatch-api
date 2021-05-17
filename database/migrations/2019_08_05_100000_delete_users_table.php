<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

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
