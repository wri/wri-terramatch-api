<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\V2\ImpactStory;
use App\Models\V2\Organisation;
use Illuminate\Support\Facades\DB;

class ImpactStorySeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ImpactStory::truncate(); 
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $organizations = Organisation::factory(3)->create();

        ImpactStory::factory(10)->create([
            'organization_id' => $organizations->random()->id,
        ]);
    }
}
