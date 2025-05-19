<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('financial_indicators', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('financial_indicators', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->nullable(false)->change();
        });
    }
};
