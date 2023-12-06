<?php

namespace App\Console\Commands\Migration;

use App\Models\V2\Disturbance;
use App\Models\V2\Invasive;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Seeding;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FullSuiteMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:full-suite {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all Data to  V2 tables';

    public function handle()
    {
        if ($this->option('fresh')) {
            Seeding::truncate();
            Disturbance::truncate();
            TreeSpecies::truncate();
            Nursery::truncate();
            NurseryReport::truncate();
            Site::truncate();
            SiteReport::truncate();
            Project::truncate();
            ProjectReport::truncate();
            UpdateRequest::truncate();
            ProjectMonitoring::truncate();
            SiteMonitoring::truncate();
            Invasive::truncate();
        }

        $args = $this->option('timestamps') ? ' --timestamps' : '';

        Artisan::call('v2migration:batch1' . $args);
        Artisan::call('v2migration:batch2' . $args);
        Artisan::call('v2migration:batch3' . $args);
    }
}
