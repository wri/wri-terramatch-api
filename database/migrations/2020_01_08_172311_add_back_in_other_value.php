<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackInOtherValue extends Migration
{
    public function up()
    {
        Schema::table("carbon_certification_versions", function (Blueprint $table) {
            $table->string("other_value")->nullable();
        });
    }

    public function down()
    {
    }
}
