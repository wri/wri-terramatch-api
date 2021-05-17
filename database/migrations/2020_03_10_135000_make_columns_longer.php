<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeColumnsLonger extends Migration
{

    public function up()
    {
        DB::statement("
            ALTER TABLE `pitch_versions`
            CHANGE COLUMN `training_type` `training_type` LONGTEXT COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL;
        ");
    }

    public function down()
    {
    }
}
