<?php

namespace App\Jobs;

use App\Models\TreeSpecies as TreeSpeciesModel;
use App\Models\TreeSpeciesVersion as TreeSpeciesVersionModel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Pitch as PitchModel;
use App\Models\PitchVersion as PitchVersionModel;
use App\Services\VersionService;

class UpdatePricePerTreeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $id = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pitchVersionService = new VersionService(new PitchModel(), new PitchVersionModel());
        $pitch = $pitchVersionService->findParent($this->id);
        if (!$pitch->child) {
            return;
        }
        $treeSpeciesVersionService = new VersionService(new TreeSpeciesModel(), new TreeSpeciesVersionModel());
        $treeSpecies = $treeSpeciesVersionService->findAllParents([["pitch_id", "=", $pitch->parent->id]]);
        if (!$treeSpecies->count()) {
            return;
        }
        $totals = [];
        foreach ($treeSpecies as $parentAndChild) {
            $key = $parentAndChild->parent->id;
            $value = total_properties($parentAndChild->child, ["site_prep", "saplings", "price_to_plant", "price_to_maintain"]);
            $totals[$key] = $value;
        }
        $pitch->child->setRawAttributes(["price_per_tree" => min($totals)]);
        $pitch->child->saveOrFail();
    }
}
