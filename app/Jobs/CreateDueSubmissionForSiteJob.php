<?php

namespace App\Jobs;

use App\Models\DueSubmission;
use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateDueSubmissionForSiteJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $site;

    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Site $site, $date)
    {
        $this->site = $site;
        $this->date = $date;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $dueSubmission = new DueSubmission();
        $dueSubmission->due_submissionable_type = Site::class;
        $dueSubmission->due_submissionable_id = $this->site->id;
        $dueSubmission->due_at = $this->date;
        $dueSubmission->saveOrFail();
    }
}
