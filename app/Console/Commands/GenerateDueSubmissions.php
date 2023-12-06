<?php

namespace App\Console\Commands;

use App\Jobs\CreateDueSubmissionForSiteJob;
use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateDueSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-control-site-due-submissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate due submissions for control sites';

    public function handle(): int
    {
        if (! $this->isFirstFridayOfMonth() && ! $this->shouldProcessControlSites()) {
            return 0;
        }

        $date = Carbon::now()->startOfMonth()->addMonths(6)->firstOfMonth(5);
        Site::isControlSite()->chunkById(100, function ($sites) use ($date) {
            $sites->each(function ($site) use ($date) {
                CreateDueSubmissionForSiteJob::dispatch($site, $date);
            });
        });

        return 0;
    }

    protected function isFirstFridayOfMonth(): bool
    {
        $firstFriday = now()->firstOfMonth(5);

        return $firstFriday->is(today());
    }

    protected function shouldProcessControlSites(): bool
    {
        return in_array(now()->month, [ Carbon::JANUARY, Carbon::JULY]);
    }
}
