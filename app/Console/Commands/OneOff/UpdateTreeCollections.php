<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\TreeSpecies\TreeSpecies;
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
        TreeSpecies::where('collection', 'restored')->update(['collection' => TreeSpecies::COLLECTION_HISTORICAL]);
    }
}
