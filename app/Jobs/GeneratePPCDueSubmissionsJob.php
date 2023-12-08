<?php

namespace App\Jobs;

use App\Models\Programme;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePPCDueSubmissionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Carbon $dueDate;

    public function __construct(int $dueMonth)
    {
        $carbonDate = Carbon::createFromFormat('m', $dueMonth);
        $this->dueDate = $carbonDate->isPast() ? $carbonDate->addYear()->firstOfMonth(5) : $carbonDate->firstOfMonth(5);
    }

    public function handle()
    {
        $dueDate = $this->dueDate;
        Programme::chunkById(100, function ($programmes) use ($dueDate) {
            $programmes->each(function ($programme) use ($dueDate) {
                CreateDueSubmissionForProgrammeJob::dispatch($programme, $dueDate);
            });
        });

        Site::excludeControlSite()->chunkById(100, function ($sites) use ($dueDate) {
            $sites->each(function ($site) use ($dueDate) {
                CreateDueSubmissionForSiteJob::dispatch($site, $dueDate);
            });
        });
    }
}
