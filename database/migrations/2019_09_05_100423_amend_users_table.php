<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AmendUsersTable extends Migration
{
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->string("job_role")->nullable();
            $table->string("facebook")->nullable();
            $table->string("twitter")->nullable();
            $table->string("linkedin")->nullable();
            $table->string("instagram")->nullable();
            $table->string("avatar")->nullable();
        });
    }

    public function down()
    {
    }
}
