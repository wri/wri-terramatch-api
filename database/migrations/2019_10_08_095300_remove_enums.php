<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class RemoveEnums extends Migration
{
    public function up()
    {
        /**
         * This section can't be done using migrations. Apparently changes on
         * tables containing enums aren't supported.
         */
        DB::statement("
            ALTER TABLE `organisation_versions`
            CHANGE COLUMN `category` `category` VARCHAR(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL DEFAULT 'both';
        ");
    }

    public function down()
    {
    }
}
