<?php

namespace App\Jobs\Terrafund;

use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundProgramme;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TerrafundCreateProgrammeDueSubmissionJob implements ShouldQueue
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
        TerrafundProgramme::chunkById(100, function ($programmes) {
            foreach ($programmes as $programme) {
                if (! $programme->skip_submission_cycle) {
                    $submission = new TerrafundDueSubmission();
                    $submission->terrafund_due_submissionable_type = TerrafundProgramme::class;
                    $submission->terrafund_due_submissionable_id = $programme->id;
                    $submission->terrafund_programme_id = $programme->id;
                    $submission->due_at = $this->due_date;
                    $submission->is_submitted = false;
                    $submission->saveOrFail();
                } else {
                    $programme->skip_submission_cycle = false;
                    $programme->saveOrFail();
                }
            }
        });
    }
}
