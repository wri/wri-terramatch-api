<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ModifyNullableStateOfColumns extends Migration
{
    public function  up()
    {
        DB::statement("
            ALTER TABLE `tree_species_versions`
            CHANGE COLUMN `owner` `owner` VARCHAR(255) COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL;
        ");
        DB::statement("
            ALTER TABLE `pitch_versions`
            CHANGE COLUMN `local_community_involvement` `local_community_involvement` TINYINT(1) NULL DEFAULT NULL;
        ");
    }

    public function down()
    {
    }
}
