<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateFundingAmountColumn extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE `filter_records` 
            CHANGE COLUMN `funding_amount` `funding_bracket` TINYINT(1) NOT NULL DEFAULT 0;
        ");
    }

    public function down()
    {
    }
}
