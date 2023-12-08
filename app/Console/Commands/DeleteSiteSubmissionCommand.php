<?php

namespace App\Console\Commands;

use App\Helpers\DeletionHelper;
use App\Models\SiteSubmission;
use Illuminate\Console\Command;

class DeleteSiteSubmissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-site-submission {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a site submission and all related data';

    public function handle(): int
    {
        $submission = SiteSubmission::where('id', $this->argument('id'))->firstOrFail();

        DeletionHelper::deleteSiteSubmissionAndRelations($submission);

        $this->info("Site submission $submission->id Deleted");

        return 0;
    }
}
