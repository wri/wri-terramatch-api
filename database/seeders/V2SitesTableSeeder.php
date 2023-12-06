<?php

namespace Database\Seeders;

use App\Models\V2\Sites\Site;
use Illuminate\Database\Seeder;

class V2SitesTableSeeder extends Seeder
{
    public function run()
    {
        Site::factory()
            ->count(20)
            ->create();
    }
}
