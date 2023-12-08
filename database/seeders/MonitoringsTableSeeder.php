<?php

namespace Database\Seeders;

use App\Models\Monitoring as MonitoringModel;
use Illuminate\Database\Seeder;

class MonitoringsTableSeeder extends Seeder
{
    public function run()
    {
        $monitoring = new MonitoringModel();
        $monitoring->id = 1;
        $monitoring->match_id = 2;
        $monitoring->initiator = 'pitch';
        $monitoring->stage = 'negotiating_targets';
        $monitoring->negotiating = 'pitch';
        $monitoring->created_by = 3;
        $monitoring->saveOrFail();

        $monitoring = new MonitoringModel();
        $monitoring->id = 2;
        $monitoring->match_id = 3;
        $monitoring->initiator = 'pitch';
        $monitoring->stage = 'accepted_targets';
        $monitoring->negotiating = null;
        $monitoring->created_by = 3;
        $monitoring->saveOrFail();
    }
}
