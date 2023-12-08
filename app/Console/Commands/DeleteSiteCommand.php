<?php

namespace App\Console\Commands;

use App\Helpers\DeletionHelper;
use App\Models\Site;
use Illuminate\Console\Command;

class DeleteSiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-site {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a site and all related data';

    public function handle(): int
    {
        $site = Site::where('id', $this->argument('id'))->firstOrFail();

        DeletionHelper::deleteSiteAndRelations($site);

        $this->info("Site $site->name ($site->id) Deleted");

        return 0;
    }
}
