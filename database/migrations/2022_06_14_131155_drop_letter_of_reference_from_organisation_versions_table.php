<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('organisation_versions', function (Blueprint $table) {
            $table->dropColumn('letter_of_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('organisation_versions', function (Blueprint $table) {
            $table->string('letter_of_reference')->nullable();
        });
    }
};
