<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->text('technical_narrative')->nullable()->after('restoration_strategy');
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
            $table->dropColumn('technical_narrative');
        });
    }
}
