<?php

namespace App\Console\Commands;

use App\Helpers\GeometryHelper;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectPolygon;
use App\Services\PythonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
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
        $bar = $this->output->createProgressBar(count($projects));
        $bar->start();
        foreach ($projects as $project) {
            $this->processProject($project);
            $bar->advance();
        }
        $bar->finish();
        $this->info("\nGeometry boundaries parsing completed.");
    }

    private function processProject($project)
    {
        $currentGeojson = $project->boundary_geojson;
        if ($currentGeojson) {
            if (GeometryHelper::isFeatureCollectionEmpty($currentGeojson)) {
                return;
            }
            $needsVoronoi = GeometryHelper::isOneOrTwoPointFeatures($currentGeojson);
            if ($needsVoronoi) {
                $pointWithEstArea = GeometryHelper::addEstAreaToPointFeatures($currentGeojson);
                $currentGeojson = App::make(PythonService::class)->voronoiTransformation(json_decode($pointWithEstArea));
            }
            $convexHullWkt = GeometryHelper::getConvexHull($currentGeojson);

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
