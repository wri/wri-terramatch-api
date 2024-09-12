<?php

namespace App\Console\Commands;

use App\Mail\TerrafundSiteAndNurseryReminder;
use App\Models\Terrafund\TerrafundProgramme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTerrafundSiteAndNurseryRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-terrafund-site-and-nursery-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to create Terrafund sites & nurseries';

    public function handle(): int
    {
        TerrafundProgramme::query()
            ->whereDoesntHave('terrafundSites')
            ->whereDoesntHave('terrafundNurseries')
            ->chunkById(100, function ($programmes) {
                $programmes->each(function ($programme) {
                    if ($programme->users->count()) {
                        $programme->users->each(function ($user) use ($programme) {
                            Mail::to($user->email_address)
                                ->queue(new TerrafundSiteAndNurseryReminder($programme->id, $user));
                        });
                    }
                });
            });

        return 0;
    }
}
