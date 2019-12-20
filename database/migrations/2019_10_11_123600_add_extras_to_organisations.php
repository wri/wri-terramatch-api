<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddExtrasToOrganisations extends Migration
{
    public function up()
    {
        Schema::table("organisations", function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table("organisation_versions", function (Blueprint $table) {
            $table->date("founded_at")->nullable();
        });
    }

    public function down()
    {
    }
}
