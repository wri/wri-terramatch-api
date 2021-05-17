<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GiveUsersUnsubscribes extends Migration
{
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->boolean("is_subscribed")->default(1);
            $table->boolean("has_consented")->default(1);
        });
    }

    public function down()
    {
    }
}
