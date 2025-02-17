<?php

namespace App\Console\Commands;

use App\Jobs\SendWeeklyPolygonUpdateNotificationsJob;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Console\Command;

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
        SitePolygon::chunkById(100, function ($sitePolygons) {
            $sitePolygons->each(function (SitePolygon $sitePolygon) {
                SendWeeklyPolygonUpdateNotificationsJob::dispatchSync($sitePolygon);
            });
        });
    }
}
