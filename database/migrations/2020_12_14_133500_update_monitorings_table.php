<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateMonitoringsTable extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE `monitorings`
            CHANGE COLUMN `stage` `stage` ENUM(
                'awaiting_visibilities',
                'awaiting_targets',
                'negotiating_targets',
                'accepted_targets'
            ) NOT NULL;
        ");
    }

    public function down()
    {
    }
}
