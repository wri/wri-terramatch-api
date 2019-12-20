<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddMoreDataToTeamMembers extends Migration
{
    public function up()
    {
        Schema::table("team_members", function(Blueprint $table) {
            $table->string("email_address")->nullable();
            $table->string("phone_number")->nullable();
        });
        Schema::table("users", function(Blueprint $table) {
            $table->string("phone_number")->nullable();
        });
    }

    public function down()
    {
    }
}
