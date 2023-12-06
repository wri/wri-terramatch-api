<?php

namespace App\Console\Commands\Migration;

use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ProjectBatchMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:programme-batch  {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all Programme Data to  V2 Projects';

    public function handle()
    {
        if ($this->option('fresh')) {
            Project::truncate();
        }
        $args = $this->option('timestamps') ? ' --timestamps' : '';

        Artisan::call('v2migration:programme-ppc' . $args);
        Artisan::call('v2migration:programme-terrafund' . $args);
    }
}
