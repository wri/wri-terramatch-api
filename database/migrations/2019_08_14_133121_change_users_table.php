<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeUsersTable extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE `users`
            CHANGE COLUMN `first_name` `first_name` VARCHAR(255) COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL,
            CHANGE COLUMN `last_name` `last_name` VARCHAR(255) COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL,
            CHANGE COLUMN `password` `password` VARCHAR(255) COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL;
        ");
    }

    public function down()
    {
    }
}
