<?php

namespace App\Console\Commands\Migration;

use App\Models\Programme;
use App\Models\Site as PPCSite;
use App\Models\V2\BaselineMonitoring\SiteMetric;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use Illuminate\Console\Command;

class SiteMonitoringMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:site-monitoring {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Site Monitoring Data only to  V2 Sites';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            SiteMonitoring::truncate();
        }

        $collection = SiteMetric::all();

        foreach ($collection as $metric) {
            $count++;
            $map = $this->mapValues($metric);

            if(!empty(data_get( $map,  'site_id'))) {
                $siteMonitoring = SiteMonitoring::create($map);
                $created++;

                if ($this->option('timestamps')) {
                    $siteMonitoring->created_at = $metric->created_at;
                    $siteMonitoring->updated_at = $metric->updated_at;
                    $siteMonitoring->save();
                }
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapValues(SiteMetric $metric): array
    {
        $data = [
            'old_id' => $metric->id,
            'old_model' => SiteMetric::class,

            'status' =>data_get($metric, 'status'),
            'tree_count' => data_get($metric, 'tree_count'),
            'tree_cover' => data_get($metric, 'tree_cover'),
            'field_tree_count' => data_get($metric, 'field_tree_count'),
            'field_tree_count' => data_get($metric, 'field_tree_count'),
        ];

        $site = Site::where('old_model', $metric->monitorable_type)
            ->where('old_id',$metric->monitorable_id)
            ->first();

        if (! empty($site)) {
            $data['site_id'] = $site->id;
            $data['framework_key'] = $site->framework_key;
        }

        return $data;
    }
}
