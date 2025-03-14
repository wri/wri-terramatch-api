<?php

namespace App\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssociateExactMatchTrees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update-site-polygons-plant-start-field';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update site polygons plant start field with the stablishment date from the site';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('site_polygon')
            ->join('v2_sites', 'site_polygon.site_id', '=', 'v2_sites.uuid')
            ->whereNull('site_polygon.plantstart')
            ->whereNotNull('v2_sites.start_date')
            ->update(['site_polygon.plantstart' => DB::raw('v2_sites.start_date')]);

        $this->info('Site polygons plant start field updated successfully.');
    }
}
