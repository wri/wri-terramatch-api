<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Console\Command;

class UpdateProjectReportTreeCollections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update-project-report-tree-collections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates collections on tree species for project and project report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // The only framework that currently sets tree species on project report is PPC, so we can blanket update them
        TreeSpecies::withTrashed()
            ->where('speciesable_type', ProjectReport::class)
            ->update(['collection' => 'nursery-seedling']);

        // Updating existing questions is also required to match changes to linked-fields.php; this doesn't happen automatically
        FormQuestion::where('linked_field_key', 'pro-rep-rel-tree-species')->update(['collection' => 'nursery-seedling']);
    }
}
