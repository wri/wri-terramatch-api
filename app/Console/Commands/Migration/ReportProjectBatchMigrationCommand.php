<?php

namespace App\Console\Commands\Migration;

use App\Models\V2\Projects\ProjectReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ReportProjectBatchMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:report-project-batch  {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all Programme Submissions Data to  V2 Projects Reports';

    public function handle()
    {
        if ($this->option('fresh')) {
            ProjectReport::truncate();
        }

        $args = $this->option('timestamps') ? ' --timestamps' : '';

        Artisan::call('v2migration:report-project-ppc' . $args);
        Artisan::call('v2migration:report-project-terrafund' . $args);
    }
}
