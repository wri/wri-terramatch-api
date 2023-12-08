<?php

namespace Database\Seeders;

use App\Models\Programme;
use App\Models\SatelliteMonitor;
use App\Models\Site;
use Illuminate\Database\Seeder;

class SatelliteMonitorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $satMonitoring = new SatelliteMonitor();
        $satMonitoring->id = 1;
        $satMonitoring->satellite_monitorable_type = Programme::class;
        $satMonitoring->satellite_monitorable_id = 1;
        $satMonitoring->map = DatabaseSeeder::seedRandomObject('image');
        $satMonitoring->created_at = now()->subDay();
        $satMonitoring->saveOrFail();

        $satMonitoring = new SatelliteMonitor();
        $satMonitoring->id = 2;
        $satMonitoring->satellite_monitorable_type = Site::class;
        $satMonitoring->satellite_monitorable_id = 1;
        $satMonitoring->map = DatabaseSeeder::seedRandomObject('image');
        $satMonitoring->created_at = now()->subDay();
        $satMonitoring->saveOrFail();

        $satMonitoring = new SatelliteMonitor();
        $satMonitoring->id = 3;
        $satMonitoring->satellite_monitorable_type = Programme::class;
        $satMonitoring->satellite_monitorable_id = 1;
        $satMonitoring->map = DatabaseSeeder::seedRandomObject('image');
        $satMonitoring->saveOrFail();

        $satMonitoring = new SatelliteMonitor();
        $satMonitoring->id = 4;
        $satMonitoring->satellite_monitorable_type = Site::class;
        $satMonitoring->satellite_monitorable_id = 1;
        $satMonitoring->map = DatabaseSeeder::seedRandomObject('image');
        $satMonitoring->saveOrFail();
    }
}
