<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateEnumVisibilities extends Migration
{

    public function up()
    {
        foreach (["offers", "pitches"] as $table) {
            DB::statement("
                -- 1/4
                ALTER TABLE `" . $table . "`
                CHANGE COLUMN `visibility` `visibility`
                ENUM(
                    'archived', 'looking', 'talking', 'entering_contracts',
                    'partially_funded', 'partially_invested_funded', 'fully_funded', 'fully_invested_funded', 'finished'
                ) COLLATE 'utf8mb4_unicode_ci' NOT NULL DEFAULT 'looking';
            ");
            DB::statement("
                -- 2/4
                UPDATE `" . $table . "`
                SET `visibility` = 'partially_invested_funded' WHERE `visibility` = 'partially_funded';
            ");
            DB::statement("
                -- 3/4
                UPDATE `" . $table . "`
                SET `visibility` = 'fully_invested_funded' WHERE `visibility` = 'fully_funded';
            ");
            DB::statement("
                -- 4/4
                ALTER TABLE `" . $table . "`
                CHANGE COLUMN `visibility` `visibility`
                ENUM(
                    'archived', 'looking', 'talking', 'entering_contracts',
                    'partially_invested_funded', 'fully_invested_funded', 'finished'
                ) COLLATE 'utf8mb4_unicode_ci' NOT NULL DEFAULT 'looking';
            ");
        }
    }

    public function down()
    {
    }
}
