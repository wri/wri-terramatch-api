<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrganisationTypesToFundingProgrammesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('funding_programmes', function (Blueprint $table) {
            $table->text('organisation_types')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('funding_programmes', function (Blueprint $table) {
            $table->dropColumn('organisation_types');
        });
    }
}
