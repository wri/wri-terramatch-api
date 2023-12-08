<?php

namespace App\Console\Commands\Migration;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteMonitoring;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Batch2MigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:batch2  {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all Report and Monitoring Data to  V2 tables';

    public function handle()
    {
        if ($this->option('fresh')) {
            SiteMonitoring::truncate();
            ProjectMonitoring::truncate();
            NurseryReport::truncate();
            SiteReport::truncate();
            ProjectReport::truncate();
        }

        $args = $this->option('timestamps') ? ' --timestamps' : '';

        Artisan::call('v2migration:report-project-batch' . $args);
        Artisan::call('v2migration:report-site-batch' . $args);
        Artisan::call('v2migration:report-nursery-terrafund' . $args);
        Artisan::call('v2migration:monitoring-batch' . $args);
        Artisan::call('v2migration:update-requests');
    }
}
