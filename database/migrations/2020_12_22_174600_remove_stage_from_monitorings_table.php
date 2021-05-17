<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveStageFromMonitoringsTable extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE `monitorings`
            CHANGE COLUMN `stage` `stage` ENUM(
                'awaiting_visibilities',
                'negotiating_targets',
                'accepted_targets'
            ) NOT NULL;
        ");
    }

    public function down()
    {
    }
}
