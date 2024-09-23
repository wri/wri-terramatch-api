<?php

namespace App\Console\Commands;

use App\Models\V2\ProjectPitch;
use Illuminate\Console\Command;

class V2SeparateInterventionMethodsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-separate-intervention-methods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Separate out intervention types into land use and restoration strategy';

    public function handle()
    {
        /*
         * needs mapping
         * enrichment-planting
         * applied-nucleation
         */

        ProjectPitch::chunk(500, function ($chunk) {
            $landUseList = ['agroforest', 'mangrove', 'natural-forest', 'silvopasture', 'peatland', 'riparian-area-or-wetland', 'urban-forest', 'woodlot-or-plantation'];
            $restorationList = ['assisted-natural-regeneration', 'direct-seeding', 'tree-planting'];

            foreach ($chunk as $pitch) {
                $landUse = [];
                $strategy = [];

                if ($pitch->restoration_intervention_types) {
                    foreach ($pitch->restoration_intervention_types as $item) {
                        switch($item) {
                            case 'agroforestry':
                                $landUse[] = 'agroforest';

                                break;
                            case 'mangrove-restoration':
                            case 'mangrove-tree-restoration':
                                $landUse[] = 'mangrove';

                                break;
                            case 'riparian-restoration':
                            case 'wetland-riparian':
                                $landUse[] = 'riparian-area-or-wetland';

                                break;
                            case 'peatland-restoration':
                                $landUse[] = 'peatland';

                                break;
                            case 'reforestation':
                            case 'enrichment-planting':
                            case 'applied-nucleation':
                            case 'applied-nucleation-tree-island':
                                $strategy[] = 'tree-planting';

                                break;
                            default:
                                if (in_array($item, $landUseList)) {
                                    $landUse[] = $item;
                                } elseif (in_array($item, $restorationList)) {
                                    $strategy[] = $item;
                                }
                        }
                    }

                    $pitch->land_use_types = $landUse;
                    $pitch->restoration_strategy = $strategy;
                    $pitch->save();
                }
            }
        });
    }
}
