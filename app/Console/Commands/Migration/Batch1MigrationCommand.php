<?php

namespace App\Console\Commands\Migration;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Batch1MigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:batch1  {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all Programme, Sites and Nurseries Data to  V2 tables';

    public function handle()
    {
        if ($this->option('fresh')) {
            Site::truncate();
            Nursery::truncate();
            Project::truncate();
        }

        $args = $this->option('timestamps') ? ' --timestamps' : '';

        Artisan::call('v2migration:programme-batch' . $args);
        Artisan::call('v2migration:site-batch' . $args);
        Artisan::call('v2migration:nursery-terrafund' . $args);
        Artisan::call('v2migration:project-users');
    }
}
