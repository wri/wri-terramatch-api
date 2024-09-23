<?php

namespace App\Console\Commands\Migration;

use App\Models\Programme;
use App\Models\Site as PPCSite;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SitePPCMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:site-ppc {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate PPC Site Data only to  V2 Sites';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            Site::truncate();
        }

        $collection = PPCSite::all();

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

    private function mapValues(PPCSite $site): array
    {
        $data = [
            'old_model' => PPCSite::class,
            'old_id' => $site->id,
            'framework_key' => 'ppc',

            'name' => data_get($site, 'name'),
            'description' => data_get($site, 'description'),
            'control_site' => data_get($site, 'control_site'),
            'history' => data_get($site, 'history'),
            'boundary_geojson' => data_get($site, 'boundary_geojson'),
            'start_date' => data_get($site, 'establishment_date'),
            'end_date' => data_get($site, 'end_date'),
            'land_tenures' => $this->handleLandTenure($site),
            'status' => EntityStatusStateMachine::APPROVED,
            'survival_rate_planted' => data_get($site, 'aim_survival_rate'),
            'technical_narrative' => data_get($site, 'technical_narrative'),
            'direct_seeding_survival_rate' => data_get($site, 'aim_direct_seeding_survival_rate'),
            'a_nat_regeneration_trees_per_hectare' => data_get($site, 'aim_natural_regeneration_trees_per_hectare'),
            'a_nat_regeneration' => data_get($site, 'aim_natural_regeneration_hectares'),
            'planting_pattern' => data_get($site, 'planting_pattern'),
            'land_use_types' => $site->siteRestorationMethods()->count() > 0
                ? $this->handleLandUse(
                    $site->siteRestorationMethods()
                        ->pluck('key')
                        ->toArray()
                )
                : [],
            'restoration_strategy' => $site->siteRestorationMethods()->count() > 0
                ? $this->handleRestorationStrategy(
                    $site->siteRestorationMethods()
                        ->pluck('key')
                        ->toArray()
                )
                : [],
            'soil_condition' => data_get($site, 'aim_soil_condition'),
            'aim_year_five_crown_cover' => data_get($site, 'aim_year_five_crown_cover'),
            'aim_number_of_mature_trees' => data_get($site, 'aim_number_of_mature_trees'),

        ];

        $project = Project::where('old_model', Programme::class)
            ->where('old_id', $site->programme_id)
            ->first();

        if (! empty($project)) {
            $data['project_id'] = $project->id;
        }

        return $data;
    }

    private function handleLandTenure(PPCSite $site): array
    {
        $list = [];
        foreach ($site->landTenures as $tenure) {
            $list[] = $tenure->key;
        }

        return $list;
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
            'peatland',
        ];

        $list = [];
        foreach ($rawList as $item) {
            switch($item) {
                case 'agroforestry':
                    $val = 'agroforest';

                    break;
                case 'mangrove_tree_restoration':
                    $val = 'mangrove';

                    break;
                case 'peatland_restoration':
                    $val = 'peatland';

                    break;
                case 'wetland_riparian':
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
                case 'applied_nucleation_tree_island':
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
