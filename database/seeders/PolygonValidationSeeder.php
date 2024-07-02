<?php

namespace Database\Seeders;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Database\Seeder;

class PolygonValidationSeeder extends Seeder
{
    public const TEST_PROJECTS_FILE = 'database/seeders/Data/polygon_validation_projects.json';

    public function run(): void
    {
        $testProjects = json_decode(file_get_contents(self::TEST_PROJECTS_FILE), true);
        foreach ($testProjects as $projectDef) {
            $project = Project::factory()->create([
                'country' => $projectDef['country'],
                'total_hectares_restored_goal' => $projectDef['total_hectares_restored_goal'] ?? 0,
            ]);

            foreach ($projectDef['sites'] as $siteDef) {
                $site = Site::factory()->create([
                    'uuid' => $siteDef['uuid'],
                    'project_id' => $project->id,
                ]);

                foreach ($siteDef['geometry'] ?? [] as $geometryDef) {
                    $geojsonString = $geometryDef['geojson'];
                    $geometry = PolygonGeometry::factory()->geojson($geojsonString)->create();

                    SitePolygon::factory()->site($site)->geometry($geometry)->create([
                        'calc_area' => $geometryDef['calc_area'] ?? 0,
                    ]);
                }
            }
        }
    }
}
