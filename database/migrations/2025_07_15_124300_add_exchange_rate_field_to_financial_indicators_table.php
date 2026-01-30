<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('financial_indicators', function (Blueprint $table) {
            $table->decimal('exchange_rate', 15, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('financial_indicators', function (Blueprint $table) {
            $table->dropColumn('exchange_rate');
        });
    }
};
