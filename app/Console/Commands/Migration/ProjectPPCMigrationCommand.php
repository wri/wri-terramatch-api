<?php

namespace App\Console\Commands\Migration;

use App\Models\Programme;
use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;

class ProjectPPCMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:programme-ppc {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate PPC Programme Data only to  V2 Projects';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            Project::truncate();
        }

        $collection = Programme::all();

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

    private function mapValues(Programme $programme): array
    {
        $data = [
            'old_model' => Programme::class,
            'old_id' => $programme->id,
            'framework_key' => 'ppc',

            'name' => data_get($programme, 'name'),
            'status' => Project::STATUS_APPROVED,
            'organisation_id' => data_get($programme, 'organisation_id'),
            'boundary_geojson' => data_get($programme, 'boundary_geojson'),
            'country' => data_get($programme, 'country'),
            'continent' => data_get($programme, 'continent'),
            'planting_end_date' => data_get($programme, 'end_date'),
            'work_day_count' => data_get($programme, ''),//TODO: sheet says from socioeconomic benefits but this is a file location
        ];

        $aim = $programme->aim;
        if (! empty($aim)) {
            $data['total_hectares_restored_goal'] = data_get($aim, 'restoration_hectares');
            $data['trees_grown_goal'] = data_get($aim, 'year_five_trees');
            $data['survival_rate'] = data_get($aim, 'survival_rate');
            $data['year_five_crown_cover'] = data_get($aim, 'year_five_crown_cover');
        }

        return $data;
    }
}
