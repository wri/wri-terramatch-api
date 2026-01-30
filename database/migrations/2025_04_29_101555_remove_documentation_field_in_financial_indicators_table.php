<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('financial_indicators', function (Blueprint $table) {
            $table->dropColumn('documentation');
        });
    }

    public function down()
    {
        Schema::table('financial_indicators', function (Blueprint $table) {
            $table->string('documentation')->nullable();
        });
    }
};
