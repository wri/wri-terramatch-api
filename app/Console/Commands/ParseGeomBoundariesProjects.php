<?php

namespace App\Console\Commands;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectPolygon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ParseGeomBoundariesProjects extends Command
{
    protected $signature = 'parse:project-boundaries {framework_key}';

    protected $description = 'Parse string geojsons of projects and project pitches by framework_key';

    public function handle()
    {
        $frameworkKey = $this->argument('framework_key');
        $this->info($frameworkKey);
        $projects = Project::where('framework_key', $frameworkKey)
        ->whereNotNull('boundary_geojson')
        ->where('boundary_geojson', '!=', 'null')
        ->get();
        foreach ($projects as $project) {
            $this->processProject($project);
        }

        $this->info("\nGeometry boundaries parsing completed.");
    }

    private function getConvexHull($geoJson)
    {
        $geoJsonString = is_array($geoJson) ? json_encode($geoJson) : $geoJson;
        $query = 'SELECT ST_AsText(ST_CONVEXHULL(ST_GeomFromGeoJSON(:geojson))) as wkt';
        $result = DB::select($query, ['geojson' => $geoJsonString]);

        return $result[0]->wkt ?? null;
    }

    private function processProject($project)
    {
        if ($project->boundary_geojson) {
            $convexHullWkt = $this->getConvexHull($project->boundary_geojson);

            if ($convexHullWkt) {
                $polygonGeometry = new PolygonGeometry();
                $polygonGeometry->geom = DB::raw("ST_GeomFromText('" . $convexHullWkt . "')");
                $polygonGeometry->save();
                ProjectPolygon::create([
                    'poly_uuid' => $polygonGeometry->uuid,
                    'entity_type' => Project::class,
                    'entity_id' => $project->id,
                    'last_modified_by' => 'system',
                    'created_by' => 'system',
                ]);
            }
        }
    }
}
