<?php

namespace App\Console\Commands\Migration;

use App\Models\V2\Disturbance;
use App\Models\V2\Invasive;
use App\Models\V2\Seeding;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Batch3MigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:batch3 {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all Relation Data to  V2 tables (seeding, tree Species etc.)';

    public function handle()
    {
        if ($this->option('fresh')) {
            Seeding::truncate();
            Disturbance::truncate();
            Invasive::truncate();
            TreeSpecies::truncate();
        }

        $args = $this->option('timestamps') ? ' --timestamps' : '';

        Artisan::call('v2migration:seeding' . $args);
        Artisan::call('v2migration:disturbance' . $args);
        Artisan::call('v2migration:invasive' . $args);
        Artisan::call('v2migration:tree-species' . $args);
    }
}
