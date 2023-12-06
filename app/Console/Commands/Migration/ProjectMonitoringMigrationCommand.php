<?php

namespace App\Console\Commands\Migration;

use App\Models\Programme;
use App\Models\Site as PPCSite;
use App\Models\V2\BaselineMonitoring\ProjectMetric;
use App\Models\V2\BaselineMonitoring\SiteMetric;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use Illuminate\Console\Command;

class ProjectMonitoringMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:project-monitoring {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Project Monitoring Data only to  V2 Sites';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            ProjectMonitoring::truncate();
        }

        $collection = ProjectMetric::all();

        foreach ($collection as $metric) {
            $count++;
            $map = $this->mapValues($metric);

            if (! empty($map)) {
                $projectMonitoring = ProjectMonitoring::create($map);
                $created++;

                if ($this->option('timestamps')) {
                    $projectMonitoring->created_at = $metric->created_at;
                    $projectMonitoring->updated_at = $metric->updated_at;
                    $projectMonitoring->save();
                }
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapValues(ProjectMetric $metric): ?array
    {
        $data = [
            'old_id' => $metric->id,
            'old_model' => ProjectMetric::class,
            'status' => data_get($metric, 'status'),
            'total_hectares' => data_get($metric, 'total_hectares'),
            'ha_mangrove' => data_get($metric, 'ha_mangrove'),
            'ha_assisted' => data_get($metric, 'ha_assisted'),
            'ha_agroforestry' => data_get($metric, 'ha_agroforestry'),
            'ha_reforestation' => data_get($metric, 'ha_reforestation'),
            'ha_peatland' => data_get($metric, 'ha_peatland'),
            'ha_riparian' => data_get($metric, 'ha_riparian'),
            'ha_enrichment' => data_get($metric, 'ha_enrichment'),
            'ha_nucleation' => data_get($metric, 'ha_nucleation'),
            'ha_silvopasture' => data_get($metric, 'ha_silvopasture'),
            'ha_direct' => data_get($metric, 'ha_direct'),
            'tree_count' => data_get($metric, 'tree_count'),
            'tree_cover' => data_get($metric, 'tree_cover'),
            'tree_cover_loss' => data_get($metric, 'tree_cover_loss'),
            'carbon_benefits' => data_get($metric, 'carbon_benefits'),
            'number_of_esrp' => data_get($metric, 'number_of_esrp'),
            'field_tree_count' => data_get($metric, 'field_tree_count'),
            'field_tree_regenerated' => data_get($metric, 'field_tree_regenerated'),
            'field_tree_survival_percent' => data_get($metric, 'field_tree_survival_percent'),
            'last_updated' => data_get($metric, 'last_updated'),
        ];

        $project = Project::where('old_model', $metric->monitorable_type)
            ->where('old_id',$metric->monitorable_id)
            ->first();

        if (! empty($project)) {
            $data['project_id'] = $project->id;
            $data['framework_key'] = $project->framework_key;

            return $data;
        }

        return null;
    }
}
