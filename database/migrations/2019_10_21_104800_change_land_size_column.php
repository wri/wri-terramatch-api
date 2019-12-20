<?php

use Illuminate\Database\Migrations\Migration;

class ChangeLandSizeColumn extends Migration
{
    public function up()
    {
        /**
         * This section can't be done using migrations. Apparently changes on
         * tables containing enums aren't supported.
         */
        DB::statement("
            ALTER TABLE `pitch_versions` 
            CHANGE COLUMN `land_size` `land_size` VARCHAR(255) NOT NULL;
        ");
        DB::statement("
            ALTER TABLE `offers` 
            CHANGE COLUMN `land_size` `land_size` VARCHAR(255) NOT NULL;
        ");
    }

    public function down()
    {
    }
}
