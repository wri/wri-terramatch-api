<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveOldReportingTimes extends Migration
{
    public function up()
    {
        DB::statement("
            UPDATE `offers` SET `reporting_frequency` = 'gt_quarterly'
            WHERE `reporting_frequency` = 'monthly' OR `reporting_frequency` = 'gt_monthly';
        ");
        DB::statement("
            UPDATE `pitch_versions` SET `reporting_frequency` = 'gt_quarterly'
            WHERE `reporting_frequency` = 'monthly' OR `reporting_frequency` = 'gt_monthly';
        ");
    }

    public function down()
    {
    }
}
