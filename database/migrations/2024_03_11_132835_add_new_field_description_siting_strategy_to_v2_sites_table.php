<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldDescriptionSitingStrategyToV2SitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->text('description_siting_strategy')->nullable();
            $table->text('siting_strategy')->nullable();
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
            $table->dropColumn(['description_siting_strategy, siting_strategy']);
        });
    }
}
