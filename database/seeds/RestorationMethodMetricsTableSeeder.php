<?php

use Illuminate\Database\Seeder;
use App\Models\RestorationMethodMetric as RestorationMethodMetricModel;

class RestorationMethodMetricsTableSeeder extends Seeder
{
    public function run()
    {
        $restorationMethodMetric = new RestorationMethodMetricModel();
        $restorationMethodMetric->id = 1;
        $restorationMethodMetric->pitch_id = 1;
        $restorationMethodMetric->saveOrFail();
    }
}
