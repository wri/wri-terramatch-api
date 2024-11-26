<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Console\Command;

class UpdateTreeCollections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update-tree-collections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes old / missing collection names and updates to a correct value.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating collections in v2_tree_species');
        TreeSpecies::withoutTimestamps(function () {
            TreeSpecies::withTrashed()->where('speciesable_type', ProjectPitch::class)
                ->update(['collection' => TreeSpecies::COLLECTION_PLANTED]);
            TreeSpecies::withTrashed()->where('speciesable_type', Project::class)->where('collection', 'primary')
                ->update(['collection' => TreeSpecies::COLLECTION_PLANTED]);
            TreeSpecies::withTrashed()->where('speciesable_type', Organisation::class)
                ->update(['collection' => TreeSpecies::COLLECTION_HISTORICAL]);
            TreeSpecies::withTrashed()->where('speciesable_type', SiteReport::class)->where('collection', null)
                ->update(['collection' => TreeSpecies::COLLECTION_NON_TREE]);
        });

        $this->info('Updating collections in v2_update_requests content');
        // This is kind of a hassle; fortunately, the only model type above that has bad data embedded in update requests
        // is Project
        UpdateRequest::withoutTimestamps(function () {
            $updateRequests = UpdateRequest::where('updaterequestable_type', Project::class)
                ->where('content', 'LIKE', '%"collection":"primary"%')
                ->get();
            foreach ($updateRequests as $updateRequest) {
                $content = $updateRequest->content;
                foreach (array_keys($content) as $key) {
                    $collections = data_get($content, "$key.*.collection");
                    if (is_array($collections) && in_array('primary', $collections)) {
                        data_set($content, "$key.*.collection", TreeSpecies::COLLECTION_PLANTED);
                    }
                }

                $updateRequest->update(['content' => $content]);
            }
        });
    }
}
