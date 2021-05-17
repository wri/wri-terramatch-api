<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveEnums extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE `organisation_versions`
            CHANGE COLUMN `category` `category` VARCHAR(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL DEFAULT 'both';
        ");
    }

    public function down()
    {
    }
}
