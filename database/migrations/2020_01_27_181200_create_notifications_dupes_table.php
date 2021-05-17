<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsDupesTable extends Migration
{
    public function up()
    {
        Schema::create("notifications_buffer", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("identifier");
            $table->timestamps();
        });
    }

    public function down()
    {
    }
}
