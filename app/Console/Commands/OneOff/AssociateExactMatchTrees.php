<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Console\Command;

class AssociateExactMatchTrees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:associate-exact-match-trees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update tree species rows without a taxon_id but that do have an exact match in the backbone.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        TreeSpecies::withoutTimestamps(function () {
            $query = TreeSpecies::withTrashed()
                ->join('tree_species_research', 'v2_tree_species.name', '=', 'tree_species_research.scientific_name')
                ->where('v2_tree_species.taxon_id', null);
            $this->withProgressBar((clone $query)->count(), function ($progressBar) use ($query) {
                $query->chunkById(100, function ($trees) use ($progressBar) {
                    foreach ($trees as $tree) {
                        TreeSpecies::where('id', $tree->id)->update(['taxon_id' => $tree->taxon_id]);
                        $progressBar->advance();
                    }
                });
            });
        });
    }
}
