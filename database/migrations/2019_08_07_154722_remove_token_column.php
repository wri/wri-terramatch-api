<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveTokenColumn extends Migration
{
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("remember_token");
        });
    }

    public function down()
    {
    }
}
