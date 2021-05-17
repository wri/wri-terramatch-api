<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToOrganisationVersionsTable extends Migration
{
    public function up()
    {
        Schema::table("organisation_versions", function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down()
    {
    }
}
