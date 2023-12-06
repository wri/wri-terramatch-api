<?php

namespace Database\Seeders;

use App\Models\SatelliteMap as SatelliteMapModel;
use Illuminate\Database\Seeder;

class SatelliteMapsTableSeeder extends Seeder
{
    public function run()
    {
        $progressUpdate = new SatelliteMapModel();
        $progressUpdate->id = 1;
        $progressUpdate->monitoring_id = 2;
        $progressUpdate->alt_text = 'Foo bar baz qux norf';
        $progressUpdate->map = DatabaseSeeder::seedRandomObject('image');
        $progressUpdate->created_by = 2;
        $progressUpdate->saveOrFail();
    }
}
