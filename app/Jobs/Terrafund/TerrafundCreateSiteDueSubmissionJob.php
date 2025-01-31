<?php

namespace App\Jobs\Terrafund;

use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundSite;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TerrafundCreateSiteDueSubmissionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Carbon $due_date;

    public function __construct()
    {
        $this->due_date = Carbon::now()->addMonth()->startOfDay();
    }

    public function handle()
    {
        TerrafundSite::chunkById(100, function ($sites) {
            foreach ($sites as $site) {
                if (! $site->skip_submission_cycle) {
                    $submission = new TerrafundDueSubmission();
                    $submission->terrafund_due_submissionable_type = TerrafundSite::class;
                    $submission->terrafund_due_submissionable_id = $site->id;
                    $submission->due_at = $this->due_date;
                    $submission->is_submitted = false;
                    $submission->saveOrFail();
                } else {
                    $site->skip_submission_cycle = false;
                    $site->saveOrFail();
                }
            }
        });
    }
}
