<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSocialFromPitches extends Migration
{
    public function  up()
    {
        Schema::table("pitch_versions", function (Blueprint $table) {
            $table->dropColumn("facebook");
            $table->dropColumn("twitter");
            $table->dropColumn("linkedin");
            $table->dropColumn("instagram");
        });
    }

    public function down()
    {
    }
}
