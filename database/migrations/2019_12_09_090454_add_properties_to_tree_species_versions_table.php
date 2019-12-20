<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPropertiesToTreeSpeciesVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tree_species_versions', function (Blueprint $table) {
            $table->float("saplings")->after('price_to_maintain');
            $table->float('site_prep')->after("saplings");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tree_species_versions', function (Blueprint $table) {
            $table->dropColumn(['saplings', 'site_prep']);
        });
    }
}
