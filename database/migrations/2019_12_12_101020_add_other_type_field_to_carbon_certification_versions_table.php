<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherTypeFieldToCarbonCertificationVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carbon_certification_versions', function (Blueprint $table) {
            $table->string('other_type')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carbon_certification_versions', function (Blueprint $table) {
            $table->dropColumn(['other_type']);
        });
    }
}
