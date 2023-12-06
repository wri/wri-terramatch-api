<?php

namespace App\Jobs;

use App\Models\Pitch as PitchModel;
use App\Models\PitchVersion as PitchVersionModel;
use App\Models\TreeSpecies as TreeSpeciesModel;
use App\Models\TreeSpeciesVersion as TreeSpeciesVersionModel;
use App\Services\Version\VersionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdatePricePerTreeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $pitch = null;

    public function __construct(PitchModel $pitch)
    {
        $this->pitch = $pitch;
    }

    public function handle()
    {
        $pitchVersionService = new VersionService(new PitchModel(), new PitchVersionModel());
        $pitch = $pitchVersionService->findParent($this->pitch->id);
        if (! $pitch->child) {
            return;
        }
        $treeSpeciesVersionService = new VersionService(new TreeSpeciesModel(), new TreeSpeciesVersionModel());
        $treeSpecies = $treeSpeciesVersionService->findAllParents([['pitch_id', '=', $pitch->parent->id]]);
        if (! $treeSpecies->count()) {
            $pricePerTree = null;
        } else {
            $totals = [];
            foreach ($treeSpecies as $parentAndChild) {
                $key = $parentAndChild->parent->id;
                $value =
                    ($parentAndChild->child->site_prep ?? 0) +
                    ($parentAndChild->child->saplings ?? 0) +
                    ($parentAndChild->child->price_to_plant ?? 0) +
                    ($parentAndChild->child->price_to_maintain ?? 0);
                $totals[$key] = $value;
            }
            $pricePerTree = min($totals);
        }
        $pitch->child->setRawAttributes(['price_per_tree' => $pricePerTree]);
        $pitch->child->saveOrFail();
        PitchVersionModel::where('pitch_id', '=', $pitch->parent->id)
            ->where('status', '!=', 'approved')
            ->update(['price_per_tree' => null]);
    }
}
