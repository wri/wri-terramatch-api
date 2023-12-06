<?php

namespace App\Jobs;

use App\Models\DueSubmission;
use App\Models\Programme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateDueSubmissionForProgrammeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $programme;

    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Programme $programme, $date)
    {
        $this->programme = $programme;
        $this->date = $date;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $dueSubmission = new DueSubmission();
        $dueSubmission->due_submissionable_type = Programme::class;
        $dueSubmission->due_submissionable_id = $this->programme->id;
        $dueSubmission->due_at = $this->date;
        $dueSubmission->saveOrFail();
    }
}
