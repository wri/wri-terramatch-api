<?php

namespace App\Console\Commands;

use App\Jobs\NotifyUpdateVisibilityJob;
use App\Models\Matched as MatchModel;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;

/**
 * This class triggers notifications which remind users to update their
 * visibility (AKA funding status) if their project still has a visibility of
 * "looking" three days after having matched. This runs every five minutes,
 * hence the three day and five minute offset.
 */
class CreateVisibilityNotificationsCommand extends Command
{
    protected $signature = 'create-visibility-notifications';

    protected $description = 'Creates visibility notifications';

    public function handle(): int
    {
        $past = new DateTime('now - 3 days - 5 minutes', new DateTimeZone('UTC'));
        $now = new DateTime('now - 3 days', new DateTimeZone('UTC'));
        $matches = MatchModel::where('created_at', '>', $past)->where('created_at', '<=', $now)->get();
        foreach ($matches as $match) {
            $offer = $match->interest->offer;
            if ($offer->visibility == 'looking') {
                NotifyUpdateVisibilityJob::dispatch($offer);
            }
            $pitch = $match->interest->pitch;
            if ($pitch->visibility == 'looking') {
                NotifyUpdateVisibilityJob::dispatch($pitch);
            }
        }

        return 0;
    }
}
