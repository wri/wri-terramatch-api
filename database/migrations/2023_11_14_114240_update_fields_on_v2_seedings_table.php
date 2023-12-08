<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFieldsOnV2SeedingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_seedings', function (Blueprint $table) {
            $table->renameColumn('weight', 'weight_of_sample');
            $table->bigInteger('amount')->nullable()->after('weight');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_seedings', function (Blueprint $table) {
            $table->renameColumn('weight_of_sample', 'weight');
            $table->dropColumn('amount');
        });
    }
}
