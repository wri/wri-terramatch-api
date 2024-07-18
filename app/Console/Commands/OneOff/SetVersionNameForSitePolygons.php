<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\User;
use Illuminate\Console\Command;

class SetVersionNameForSitePolygons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:set-version-name-for-site-polygons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sitePolygons = SitePolygon::all();
        foreach ($sitePolygons as $sitePolygon) {
            $user = User::find($sitePolygon->created_by)->first();
            $sitePolygon->version_name = $sitePolygon->created_at->format('j_F_Y_H_i_s').'_'.$user->full_name;
            $sitePolygon->save();
        }
    }
}
