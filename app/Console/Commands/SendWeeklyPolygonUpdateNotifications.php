<?php

namespace App\Console\Commands;

use App\Jobs\SendWeeklyPolygonUpdateNotificationsJob;
use App\Models\V2\PolygonUpdates;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendWeeklyPolygonUpdateNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-weekly-polygon-update-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs weekly polygon update notifications for SitePolygon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sitePolygonIds = PolygonUpdates::lastWeek()->distinct()->pluck('site_polygon_uuid');
        SitePolygon::whereIn('uuid', $sitePolygonIds)->chunkById(100, function (Collection $sitePolygons) {
            $sitePolygons->each(function (SitePolygon $sitePolygon) {
                Log::info('Running weekly polygon update notifications for SitePolygon: ' . $sitePolygon->uuid);
                SendWeeklyPolygonUpdateNotificationsJob::dispatchSync($sitePolygon);
            });
        });
    }
}
