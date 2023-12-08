<?php

namespace App\Console\Commands;

use App\Helpers\DeletionHelper;
use App\Models\Submission;
use Illuminate\Console\Command;

class DeleteProgrammeSubmissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-programme-submission {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a programme submission and all related data';

    public function handle(): int
    {
        $submission = Submission::where('id', $this->argument('id'))->firstOrFail();

        DeletionHelper::deleteProgrammeSubmissionAndRelations($submission);

        $this->info("Programme submission $submission->id Deleted");

        return 0;
    }
}
