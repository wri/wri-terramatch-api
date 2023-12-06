<?php

namespace App\Console\Commands;

use App\Models\LandTenure;
use App\Models\Programme;
use App\Models\Site;
use App\Models\SiteRestorationMethod;
use App\Models\SiteTreeSpecies;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportSiteCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-site-csv {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a CSV of sites';

    public function handle(): int
    {
        $csv = Reader::createFromPath(base_path('imports/') . $this->argument('filename'), 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        foreach ($records as $index => $record) {
            // find the programme - if it doesn't exist we'll skip this entry
            $programme = Programme::where('id', $record['programme identifier'])
                ->first();
            $this->warn('Skipping index ' . $index . ' - there is no programme found.');
            if (! $programme) {
                continue;
            }

            $site = new Site();
            $site->programme_id = $programme->id;
            $site->name = $record['name'];
            $site->description = $record['description'];
            $site->history = $record['history'];
            $site->establishment_date = $record['establishment date'];
            $site->boundary_geojson = $record['boundary geojson'];
            $site->end_date = $record['end date'];
            $site->technical_narrative = $record['technical narrative'];
            $site->public_narrative = $record['public narrative'];
            $site->aim_direct_seeding_survival_rate = $record['aim direct seeding survival rate'];
            $site->aim_survival_rate = $record['aim survival rate'];
            $site->aim_year_five_crown_cover = $record['aim year five crown cover'];
            $site->aim_natural_regeneration_trees_per_hectare = $record['aim natural regeneration trees per hectare'];
            $site->aim_soil_condition = $record['aim soil condition'];
            $site->aim_natural_regeneration_hectares = $record['aim natural regeneration hectares'];
            $site->planting_pattern = $record['planting pattern'];
            $site->saveOrFail();

            $site->siteRestorationMethods()->sync(
                SiteRestorationMethod::whereIn('name', explode('//', $record['restoration methods']))->pluck('id')
            );

            $site->landTenures()->sync(
                LandTenure::whereIn('name', explode('//', $record['land tenures']))->pluck('id')
            );

            foreach (explode('//', $record['tree species']) as $treeSpeciesName) {
                $species = new SiteTreeSpecies();
                $species->site_id = $site->id;
                $species->name = $treeSpeciesName;
                $species->saveOrFail();
            }

            $seedDetailNames = explode('//', $record['seed details - name']);
            $seedDetailTotalKgs = explode('//', $record['seed details - total kg']);
            $seedDetailAmounts = explode('//', $record['seed details - amount']);
            $seedDetailWeightOfSamples = explode('//', $record['seed details - weight of sample']);
            foreach ($seedDetailNames as $index => $seedDetailName) {
                $site->seedDetails()->create([
                    'name' => $seedDetailName,
                    'amount' => $seedDetailAmounts[$index],
                    'weight_of_sample' => $seedDetailWeightOfSamples[$index],
                    'site_id' => $site->id,
                ]);
            }

            $invasiveNames = explode('//', $record['invasives - name']);
            $invasiveTypes = explode('//', $record['invasives - type']);
            foreach ($invasiveNames as $index => $invasiveName) {
                $site->invasives()->create([
                    'name' => $invasiveName,
                    'type' => $invasiveTypes[$index],
                    'site_id' => $site->id,
                ]);
            }
        }

        return 0;
    }
}
