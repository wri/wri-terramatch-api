<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNotificationsTableAgain extends Migration
{
    public function up()
    {
        Schema::table("notifications", function (Blueprint $table) {
            $table->renameColumn("referenced_type", "referenced_model");
            $table->renameColumn("referenced_action_id", "referenced_model_id");
        });
    }

    public function down()
    {
    }
}
