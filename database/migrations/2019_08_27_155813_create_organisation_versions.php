<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationVersions extends Migration
{
    public function up()
    {
        Schema::create("organisation_versions", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("organisation_id")->unsigned();
            $table->enum("status",["pending", "approved", "rejected"])->default("pending");
            $table->string("name");
            $table->longText("description");
            $table->string("address_1");
            $table->string("address_2")->nullable();
            $table->string("city");
            $table->string("state")->nullable();
            $table->string("zip_code");
            $table->string("country");
            $table->string("phone_number");
            $table->string("website")->nullable();
            $table->foreign("organisation_id")->references("id")->on("organisations");
        });
    }
    
    public function down()
    {
    }
}
