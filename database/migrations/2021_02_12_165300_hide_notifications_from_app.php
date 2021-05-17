<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HideNotificationsFromApp extends Migration
{
    public function up()
    {
        Schema::table("notifications", function (Blueprint $table) {
            $table->boolean("hidden_from_app")->default(false);
            $table->index("hidden_from_app");
        });
    }

    public function down()
    {
    }
}
