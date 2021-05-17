<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
}
