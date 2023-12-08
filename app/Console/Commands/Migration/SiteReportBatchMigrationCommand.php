<?php

namespace App\Console\Commands\Migration;

use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SiteReportBatchMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:report-site-batch  {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all Site Submissions Data to  V2 Site Reports';

    public function handle()
    {
        if ($this->option('fresh')) {
            SiteReport::truncate();
        }

        $args = $this->option('timestamps') ? ' --timestamps' : '';

        Artisan::call('v2migration:report-site-ppc' . $args);
        Artisan::call('v2migration:report-site-terrafund' . $args);
    }
}
