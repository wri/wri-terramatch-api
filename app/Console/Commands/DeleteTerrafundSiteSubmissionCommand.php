<?php

namespace App\Console\Commands;

use App\Helpers\DeletionHelper;
use App\Models\Terrafund\TerrafundSiteSubmission;
use Illuminate\Console\Command;

class DeleteTerrafundSiteSubmissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-terrafund-site-submission {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a Terrafund site submission and all related data';

    public function handle(): int
    {
        $siteSubmission = TerrafundSiteSubmission::where('id', $this->argument('id'))->firstOrFail();

        DeletionHelper::deleteTerrafundSiteSubmissionAndRelations($siteSubmission);

        $this->info("Site submission $siteSubmission->id Deleted");

        return 0;
    }
}
