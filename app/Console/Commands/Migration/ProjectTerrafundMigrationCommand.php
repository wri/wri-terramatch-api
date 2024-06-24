<?php

namespace App\Console\Commands\Migration;

use App\Models\Terrafund\TerrafundProgramme;
use App\Models\V2\Projects\Project;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Console\Command;

class ProjectTerrafundMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:programme-terrafund {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Terrafund Programme Data only to  V2 Projects';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            Project::truncate();
        }
        $collection = TerrafundProgramme::all();

        foreach ($collection as $programme) {
            $count++;
            $map = $this->mapValues($programme);

            $project = Project::create($map);
            $created++;

            if ($this->option('timestamps')) {
                $project->created_at = $programme->created_at;
                $project->updated_at = $programme->updated_at;
                $project->save();
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapValues(TerrafundProgramme $programme): array
    {
        return [
            'old_model' => TerrafundProgramme::class,
            'old_id' => $programme->id,
            'framework_key' => 'terrafund',
            'organisation_id' => data_get($programme, 'organisation_id'),

            'name' => data_get($programme, 'name'),
            'status' => EntityStatusStateMachine::APPROVED,
            'project_status' => data_get($programme, 'status'),
            'boundary_geojson' => data_get($programme, 'boundary_geojson'),
            'country' => data_get($programme, 'project_country'),
            'planting_start_date' => data_get($programme, 'planting_start_date'),
            'planting_end_date' => data_get($programme, 'planting_end_date'),
            'description' => data_get($programme, 'description'),
            'budget' => data_get($programme, 'budget'),
            'history' => data_get($programme, 'history'),
            'objectives' => data_get($programme, 'objectives'),
            'environmental_goals' => data_get($programme, 'environmental_goals'),
            'socioeconomic_goals' => data_get($programme, 'socioeconomic_goals'),
            'sdgs_impacted' => data_get($programme, 'sdgs_impacted'),
            'long_term_growth' => data_get($programme, 'long_term_growth'),
            'community_incentives' => data_get($programme, 'community_incentives'),
            'jobs_created_goal' => data_get($programme, 'jobs_created'),
            'total_hectares_restored_goal' => data_get($programme, 'total_hectares_restored'),
            'trees_grown_goal' => data_get($programme, 'trees_planted'),
        ];
    }
}
