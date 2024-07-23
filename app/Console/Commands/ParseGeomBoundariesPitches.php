<?php

namespace App\Console\Commands;

use App\Models\V2\ProjectPitch;
use App\Services\PolygonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

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
                App::make(PolygonService::class)->processEntity($pitch);
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
}
