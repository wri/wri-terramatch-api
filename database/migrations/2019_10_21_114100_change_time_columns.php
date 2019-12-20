<?php

use Illuminate\Database\Migrations\Migration;

class ChangeTimeColumns extends Migration
{
    public function up()
    {
        /**
         * This section can't be done using migrations. Apparently changes on
         * tables containing enums aren't supported.
         */
        DB::statement("
            ALTER TABLE `pitch_versions`
            CHANGE COLUMN `estimated_timespan_in_years` `estimated_timespan` INT(11) NOT NULL;
        ");
        DB::statement("
            ALTER TABLE `restoration_method_metric_versions`
            CHANGE COLUMN `experience_in_years` `experience` INT(11) NOT NULL;
        ");
    }

    public function down()
    {
    }
}
