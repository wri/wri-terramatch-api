<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('site_submissions', function (Blueprint $table) {
            $table->text('technical_narrative')->nullable()->after('disturbance_information');
            $table->text('public_narrative')->nullable()->after('technical_narrative');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('site_submissions', function (Blueprint $table) {
            $table->dropColumn('technical_narrative');
            $table->dropColumn('public_narrative');
        });
    }
};
