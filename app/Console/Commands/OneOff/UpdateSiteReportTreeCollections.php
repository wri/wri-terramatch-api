<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Console\Command;

class UpdateSiteReportTreeCollections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update-site-report-tree-collections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean reports conditional data after approval';

    protected const TAXON_ID_COLLECTIONS = [
        "wfo-0000179103" => "non-tree",
        "wfo-0000182109" => "tree-planted",
        "wfo-0000199357" => "tree-planted",
        "wfo-0000588009" => "non-tree",
        "wfo-0000607909" => "tree-planted",
        "wfo-0000903570" => "non-tree",
        "wfo-0000910571" => "tree-planted",
        "wfo-4000009788" => "tree-planted",
        "wfo-0000862301" => "tree-planted",
        "wfo-0000947985" => "non-tree",
        "wfo-0000186081" => "tree-planted",
        "wfo-0000178022" => "tree-planted",
        "wfo-0000709925" => "non-tree",
        "wfo-0000173706" => "tree-planted",
        "wfo-0000164084" => "tree-planted",
        "wfo-0000371248" => "tree-planted",
        "wfo-0000235456" => "tree-planted",
        "wfo-0001085051" => "tree-planted",
        "wfo-0000473834" => "non-tree",
        "wfo-0000479905" => "non-tree",
        "wfo-0000465160" => "tree-planted",
        "wfo-0000486494" => "non-tree",
        "wfo-4000030481" => "tree-planted",
        "wfo-0001005418" => "tree-planted",
        "wfo-0000284421" => "tree-planted",
        "wfo-0001015799" => "tree-planted",
        "wfo-0000460040" => "non-tree",
        "wfo-4000033004" => "tree-planted",
        "wfo-0000164745" => "tree-planted",
        "wfo-0000178461" => "tree-planted",
        "wfo-0001026534" => "non-tree",
        "wfo-0000072744" => "non-tree",
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (self::TAXON_ID_COLLECTIONS as $taxonId => $collection) {
            $this->info("Updating Taxon ID [$taxonId, $collection]");
            $this->enforceCollection($taxonId, $collection);
        }
    }

    private function enforceCollection($taxonId, $collection)
    {
          $trees = TreeSpecies::where(['taxon_id' => $taxonId, 'speciesable_type' => SiteReport::class])->get()->groupBy('speciesable_id');
          foreach ($trees as $siteReportId => $reportTrees) {
              if ($reportTrees->count() > 1) {
                  foreach ($reportTrees->filter(fn ($tree) => $tree->collection != $collection) as $toRemove) {
                      $this->info("Deleting incorrect collections [$toRemove->speciesable_id, $toRemove->collection, $toRemove->taxon_id]");
                  }
              } else if ($reportTrees[0]->collection != $collection) {
                  $this->info("Collection should be updated to $collection [{$reportTrees[0]->speciesable_id}, {$reportTrees[0]->collection}, {$reportTrees[0]->taxon_id}]");
              }
          }
    }
}
