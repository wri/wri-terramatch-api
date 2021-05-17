<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAvatarsFromPitchesAndOffers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pitch_versions', function (Blueprint $table) {
            $table->dropColumn("avatar");
        });
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn("avatar");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
