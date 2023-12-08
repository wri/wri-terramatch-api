<?php

namespace App\Console\Commands;

use App\Jobs\NotifyTerrafundReportReminderJob;
use App\Mail\TerrafundReportReminder;
use App\Models\Terrafund\TerrafundProgramme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTerrafundReportRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-terrafund-report-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Terrafund report reminders';

    public function handle(): int
    {
        TerrafundProgramme::query()
            ->whereHas('terrafundSites')
            ->orWhereHas('terrafundNurseries')
            ->chunkById(100, function ($programmes) {
                $programmes->each(function ($programme) {
                    if ($programme->users->count()) {
                        Mail::to($programme->users->pluck('email_address'))->queue(new TerrafundReportReminder($programme->id));
                        $programme->users->each(function ($user) use ($programme) {
                            NotifyTerrafundReportReminderJob::dispatch($user, $programme);
                        });
                    }
                });
            });

        return 0;
    }
}
