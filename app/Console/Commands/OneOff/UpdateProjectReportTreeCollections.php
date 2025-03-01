<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\UpdateRequests\UpdateRequest;
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
            ->update(['collection' => TreeSpecies::COLLECTION_NURSERY]);

        // Updating existing questions is also required to match changes to linked-fields.php; this doesn't happen automatically
        FormQuestion::where('linked_field_key', 'pro-rep-rel-tree-species')->update(['collection' => TreeSpecies::COLLECTION_NURSERY]);

        // Need to update all in-progress update requests that are affected by the collection change.
        foreach (FormQuestion::where('linked_field_key', 'pro-rep-rel-tree-species')->pluck('uuid') as $questionUuid) {
            $this->info("\nUpdating update requests with question: $questionUuid");

            $query = UpdateRequest::where('content', 'like', "%\"$questionUuid\":[%");
            $this->withProgressBar((clone $query)->count(), function ($progressBar) use ($query, $questionUuid) {
                foreach (UpdateRequest::where('content', 'like', "%\"$questionUuid\":[%")->get() as $updateRequest) {
                    $content = $updateRequest->content;
                    data_set($content, "$questionUuid.*.collection", TreeSpecies::COLLECTION_NURSERY);
                    $updateRequest->update(['content' => $content]);

                    $progressBar->advance();
                }
            });
        }

        $this->info("\nFinished update request updates");
    }
}
