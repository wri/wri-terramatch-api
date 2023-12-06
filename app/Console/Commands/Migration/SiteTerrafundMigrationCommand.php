<?php

namespace App\Console\Commands\Migration;

use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SiteTerrafundMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:site-terrafund {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Terrafund Site Data only to  V2 Sites';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            Site::truncate();
        }

        $collection = TerrafundSite::all();

        foreach ($collection as $origSite) {
            $count++;
            $map = $this->mapValues($origSite);

            $site = Site::create($map);
            $created++;

            if ($this->option('timestamps')) {
                $site->created_at = $origSite->created_at;
                $site->updated_at = $origSite->updated_at;
                $site->save();
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapValues(TerrafundSite $site): array
    {
        $data = [
            'old_model' => TerrafundSite::class,
            'old_id' => $site->id,
            'framework_key' => 'terrafund',

            'name' => data_get($site, 'name'),
            'history' => data_get($site, 'disturbances'),
            'boundary_geojson' => data_get($site, 'boundary_geojson'),
            'start_date' => data_get($site, 'start_date'),
            'end_date' => data_get($site, 'end_date'),
            'land_tenures' => data_get($site, 'land_tenures'),
            'land_use_types' => $this->handleLandUse($site->restoration_methods),
            'restoration_strategy' => $this->handleRestorationStrategy($site->restoration_methods),
            'status' => Site::STATUS_APPROVED,
            'hectares_to_restore_goal' => data_get($site, 'hectares_to_restore'),
            'landscape_community_contribution' => data_get($site, 'landscape_community_contribution'),
        ];

        $project = Project::where('old_model', TerrafundProgramme::class)
            ->where('old_id', $site->terrafund_programme_id)
            ->first();

        if (! empty($project)) {
            $data['project_id'] = $project->id;
        }

        return $data;
    }

    private function handleLandUse(array $rawList = null): array
    {
        if (empty($rawList)) {
            return [];
        }

        $include = [
            'agroforest',
            'mangrove',
            'natural-forest',
            'silvopasture',
            'riparian-area-or-wetland',
            'urban-forest',
            'woodlot-or-plantation',
        ];

        $list = [];
        foreach ($rawList as $item) {
            switch($item) {
                case 'agroforestry':
                    $val = 'agroforest';

                    break;
                case 'mangrove_tree_restoration':
                case 'mangrove_restoration':
                    $val = 'mangrove';

                    break;
                case 'wetland_riparian':
                case 'riparian_restoration':
                    $val = 'riparian-area-or-wetland';

                    break;
                default:
                    $val = str_replace('_', '-', $item);
            }
            $list[$val] = $val;
        }

        return array_unique(array_keys(Arr::only($list, $include)));
    }

    private function handleRestorationStrategy(array $rawList = null): array
    {
        if (empty($rawList)) {
            return [];
        }

        $include = [
            'assisted-natural-regeneration',
            'direct-seeding',
            'tree-planting',
        ];

        $list = [];
        foreach ($rawList as $item) {
            switch($item) {
                case 'seed_dispersal_direct_seeding':
                    $val = 'direct-seeding';

                    break;
                case 'enrichment_planting':
                case 'applied_nucleation':
                case 'reforestation':
                    $val = 'tree-planting';

                    break;
                default:
                    $val = str_replace('_', '-', $item);
            }
            $list[$val] = $val;
        }

        return array_unique(array_keys(Arr::only($list, $include)));
    }
}
