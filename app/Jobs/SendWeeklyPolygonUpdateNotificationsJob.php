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

        foreach ($usersPdWithSkip as $user) {
            Mail::to($user->email_address)->queue(new PolygonUpdateNotification($user, $this->sitePolygon));
        }

        foreach ($usersManagersWithSkip as $user) {
            Mail::to($user->email_address)->queue(new PolygonUpdateNotification($user, $this->sitePolygon));
        }
    }
}
