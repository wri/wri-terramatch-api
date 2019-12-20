<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RemoveCurrencies extends Migration
{
    public function up()
    {
        Schema::table("offers", function (Blueprint $table) {
            $table->dropColumn("funding_amount_currency");
            $table->dropColumn("price_per_tree_currency");
        });
    }

    public function down()
    {
    }
}
