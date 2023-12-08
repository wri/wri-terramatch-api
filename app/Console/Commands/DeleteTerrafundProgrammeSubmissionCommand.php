<?php

namespace App\Console\Commands;

use App\Helpers\DeletionHelper;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use Illuminate\Console\Command;

class DeleteTerrafundProgrammeSubmissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-terrafund-programme-submission {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a Terrafund programme submission and all related data';

    public function handle(): int
    {
        $submission = TerrafundProgrammeSubmission::where('id', $this->argument('id'))->firstOrFail();

        DeletionHelper::deleteTerrafundProgrammeSubmissionAndRelations($submission);

        $this->info("Programme submission $submission->id Deleted");

        return 0;
    }
}
