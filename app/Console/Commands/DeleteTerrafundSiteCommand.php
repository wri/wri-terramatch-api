<?php

namespace App\Console\Commands;

use App\Helpers\DeletionHelper;
use App\Models\Terrafund\TerrafundSite;
use Illuminate\Console\Command;

class DeleteTerrafundSiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-terrafund-site {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a Terrafund site and all related data';

    public function handle(): int
    {
        $site = TerrafundSite::where('id', $this->argument('id'))->firstOrFail();

        DeletionHelper::deleteTerrafundSiteAndRelations($site);

        $this->info("Site $site->name ($site->id) Deleted");

        return 0;
    }
}
