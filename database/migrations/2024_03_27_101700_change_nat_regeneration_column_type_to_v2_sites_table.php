<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNatRegenerationColumnTypeToV2SitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->decimal('a_nat_regeneration', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->unsignedInteger('a_nat_regeneration')->nullable();
        });
    }
}
