<?php

namespace Database\Seeders;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use Illuminate\Database\Seeder;

class PolygonValidationSeeder extends Seeder
{
    public const TEST_SITE_UUID = 'bc5d87ab-4c98-42f1-9902-b2848bb466b7';

    public function run(): void
    {
        $project = Project::factory()->create([
            'country' => 'AU',
        ]);

        $site = Site::factory()->create([
            'uuid' => self::TEST_SITE_UUID,
            'project_id' => $project->id,
        ]);
    }
}
