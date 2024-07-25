<?php

namespace App\Console\Commands;

use App\Helpers\GeometryHelper;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\ProjectPolygon;
use App\Services\PythonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ParseGeomBoundariesPitches extends Command
{
    protected $signature = 'parse:pitch-boundaries {framework_key}';

    protected $description = 'Parse string geojsons of projects and project pitches by framework_key';

    public function handle()
    {
        $frameworkKey = $this->argument('framework_key');

        $projectPitches = $this->getProjectPitches($frameworkKey);
        $bar = $this->output->createProgressBar(count($projectPitches));
        $bar->start();

        foreach ($projectPitches as $pitch) {
            if ($pitch->proj_boundary && $pitch->proj_boundary !== 'null') {
                $this->processProjectPitch($pitch);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nGeometry boundaries parsing completed.");
    }

    private function getProjectPitches($frameworkKey)
    {
        return ProjectPitch::whereHas('fundingProgramme', function ($query) use ($frameworkKey) {
            $query->where('framework_key', $frameworkKey);
        })
        ->whereNotNull('proj_boundary')
        ->where('proj_boundary', '!=', 'null')
        ->get();
    }

    private function processProjectPitch($pitch)
    {
        $currentGeojson = $pitch->proj_boundary;
        if($currentGeojson) {
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
                    'entity_type' => ProjectPitch::class,
                    'entity_id' => $pitch->id,
                    'last_modified_by' => 'system',
                    'created_by' => 'system',
                ]);
            }
        }
    }
}
