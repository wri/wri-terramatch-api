<?php

namespace App\Console\Commands;

use App\Jobs\NotifyUpcomingProgressUpdateJob;
use App\Models\Target as TargetModel;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;

class SendUpcomingProgressUpdateNotifications extends Command
{
    protected $signature = 'send-upcoming-progress-update-notifications';

    protected $description = 'Sends upcoming progress update notifications';

    public function handle(): Int
    {
        $future = new DateTime('midnight + 30 days', new DateTimeZone('UTC'));
        $targets =
            TargetModel::whereNotNull('accepted_at')
            ->whereRaw('SUBSTR(start_date, 6, 5) = ?', [$future->format('m-d')])
            ->get();
        foreach ($targets as $target) {
            $hasStarted = $future >= $target->start_date;
            $hasFinished = $future > $target->finish_date;
            if ($hasStarted && ! $hasFinished) {
                NotifyUpcomingProgressUpdateJob::dispatchSync($target->monitoring);
            }
        }

        return 0;
    }
}
