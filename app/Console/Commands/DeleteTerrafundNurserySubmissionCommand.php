<?php

namespace App\Console\Commands;

use App\Helpers\DeletionHelper;
use App\Models\Terrafund\TerrafundNurserySubmission;
use Illuminate\Console\Command;

class DeleteTerrafundNurserySubmissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-terrafund-nursery-submission {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a Terrafund nursery submission and all related data';

    public function handle(): int
    {
        $submission = TerrafundNurserySubmission::where('id', $this->argument('id'))->firstOrFail();

        DeletionHelper::deleteTerrafundNurserySubmissionAndRelations($submission);

        $this->info("Nursery submission $submission->id Deleted");

        return 0;
    }
}
