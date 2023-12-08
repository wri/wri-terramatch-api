<?php

namespace App\Console\Commands\Migration;

use App\Models\V2\Sites\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SiteBatchMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:site-batch  {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all Site Data to  V2 Sites';

    public function handle()
    {
        if ($this->option('fresh')) {
            Site::truncate();
        }

        $args = $this->option('timestamps') ? ' --timestamps' : '';

        Artisan::call('v2migration:site-ppc' . $args);
        Artisan::call('v2migration:site-terrafund' . $args);
    }
}
