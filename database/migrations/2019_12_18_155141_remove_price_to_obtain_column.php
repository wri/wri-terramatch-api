<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePriceToObtainColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("tree_species_versions", function (Blueprint $table) {
            $table->dropColumn("price_to_obtain");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("tree_species_versions", function (Blueprint $table) {
            $table->float("price_to_obtain");
        });
    }
}
