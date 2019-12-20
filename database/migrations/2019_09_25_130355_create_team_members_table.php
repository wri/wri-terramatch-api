<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTeamMembersTable extends Migration
{
    public function up()
    {
        Schema::create("team_members", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("organisation_id")->unsigned();
            $table->string("first_name");
            $table->string("last_name");
            $table->string("job_role");
            $table->string("facebook")->nullable();
            $table->string("twitter")->nullable();
            $table->string("linkedin")->nullable();
            $table->string("instagram")->nullable();
            $table->string("avatar")->nullable();
            $table->foreign("organisation_id")->references("id")->on("organisations");
        });
    }

    public function down()
    {
    }
}
