<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AmendOrganisationVersionsTableAgain extends Migration
{
    public function up()
    {
        Schema::table("organisation_versions", function (Blueprint $table) {
            $table->string("type")->default("Other");
            $table->enum("category", ["funder", "developer", "both"])->default("both");
            $table->string("facebook")->nullable();
            $table->string("twitter")->nullable();
            $table->string("linkedin")->nullable();
            $table->string("instagram")->nullable();
            $table->string("avatar")->nullable();
            $table->string("cover_photo")->nullable();
        });
    }

    public function down()
    {
    }
}
