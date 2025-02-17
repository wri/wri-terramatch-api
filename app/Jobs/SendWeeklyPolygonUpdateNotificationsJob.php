<?php

namespace App\Jobs;

use App\Mail\PolygonUpdateNotification;
use App\Models\Traits\SkipRecipientsTrait;
use App\Models\V2\PolygonUpdates;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWeeklyPolygonUpdateNotificationsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use SkipRecipientsTrait;

    private $sitePolygon;

    /**
     * Create a new job instance.
     */
    public function __construct(SitePolygon $sitePolygon)
    {
        $this->sitePolygon = $sitePolygon;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $project = $this->sitePolygon->project;
        if (! $project) {
            return;
        }

        $hasPolygonUpdates = PolygonUpdates::where('site_polygon_uuid', $this->sitePolygon->uuid)->lastWeek()->count();

        if ($hasPolygonUpdates === 0) {
            return;
        }

        $usersPdWithSkip = $this->skipRecipients($project->users()->get());
        $usersManagersWithSkip = $this->skipRecipients($project->managers()->get());
        $usersPDGroupedByLocale = $usersPdWithSkip->groupBy('locale');
        $usersManagersGroupedByLocale = $usersManagersWithSkip->groupBy('locale');

        foreach ($usersPDGroupedByLocale as $locale => $users) {
            $groupedLocale['locale'] = $locale;
            Mail::to($users->pluck('email_address')->toArray())->queue(new PolygonUpdateNotification($groupedLocale, $this->sitePolygon));
        }

        foreach ($usersManagersGroupedByLocale as $locale => $users) {
            $groupedLocaleManager['locale'] = $locale;
            Mail::to($users->pluck('email_address')->toArray())->queue(new PolygonUpdateNotification($groupedLocaleManager, $this->sitePolygon));
        }
    }
}
