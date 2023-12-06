<?php

namespace App\Console\Commands\Migration;

use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Models\V2\Sites\SiteMonitoring;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MonitoringBatchMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:monitoring-batch  {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all Monitoring Data to  V2 Projects';

    public function handle()
    {
        if ($this->option('fresh')) {
            ProjectMonitoring::truncate();
            SiteMonitoring::truncate();
        }
        $args = $this->option('timestamps') ? ' --timestamps' : '';

        Artisan::call('v2migration:project-monitoring' . $args);
        Artisan::call('v2migration:site-monitoring' . $args);
    }
}
