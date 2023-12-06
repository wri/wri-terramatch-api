<?php

namespace Database\Seeders;

use App\Models\RestorationMethodMetric as RestorationMethodMetricModel;
use Illuminate\Database\Seeder;

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
