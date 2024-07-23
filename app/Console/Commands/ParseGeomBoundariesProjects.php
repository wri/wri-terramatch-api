<?php

namespace App\Console\Commands;

use App\Models\V2\Projects\Project;
use App\Services\PolygonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

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
            App::make(PolygonService::class)->processEntity($project);
            $bar->advance();
        }
        $bar->finish();
        $this->info("\nGeometry boundaries parsing completed.");
    }
}
