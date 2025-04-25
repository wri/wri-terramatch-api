<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MigrateLocationCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-location-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moves historical state / country codes in the DB to the official GADM codes for those places.';

    protected const DATA_API = 'https://data-api.globalforestwatch.org/dataset/gadm_administrative_boundaries/v4.1.85/query';
    protected const API_KEY = '349c85f6-a915-4d3d-b337-0a0dafc0e5db';

    protected const LEVEL_FIELDS = [
        Organisation::class => [
            'gadm_0_single' => ['hq_country'],
            'gadm_0_multiple' => ['countries', 'level_0_past_restoration'],
            'gadm_1_multiple' => ['states', 'level_1_past_restoration'],
        ],
        ProjectPitch::class => [
            'gadm_0_single' => ['project_country'],
            'gadm_0_multiple' => ['level_0_proposed'],
            'gadm_1_multiple' => ['states', 'level_1_proposed'],
        ],
        Project::class => [
            'gadm_0_single' => ['country'],
            'gadm_1_multiple' => ['states'],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (self::LEVEL_FIELDS as $model => $definitions) {
            $this->info("\n\nUpdating $model records...");

            /** @var Builder $query */
            $query = $model::query();
            foreach (collect($definitions)->values()->flatten() as $column) {
                $query->orWhereNotNull($column);
            }
            $this->handleMigrationSet($query, $definitions);
        }
    }

    protected function handleMigrationSet($query, $definitions)
    {
        $count = (clone $query)->count();
        $this->withProgressBar($count, function ($progressBar) use ($query, $definitions, &$chunks) {
            $query->chunk(100, function ($chunk) use ($progressBar, $definitions, &$chunks) {
                foreach ($chunk as $entity) {
                    foreach ($definitions as $type => $columns) {
                        foreach ($columns as $column) {
                            if (Str::endsWith($type, '_single')) {
                                $values = [$entity[$column]];
                            } else {
                                $values = $entity[$column];
                            }
                            if (! empty($values)) {
                                if (Str::startsWith($type, 'gadm_0')) {
                                    $this->remapCountries($values);
                                } else {
                                    $this->remapStates($values);
                                }
                            }
                        }
                    }

                    $progressBar->advance();
                }
            });
        });
    }

    protected function remapCountries($countries)
    {
        $update = [];
        foreach ($countries as $country) {
            if (! empty($country)) {
                $gadm = $this->findCountry($country);
                if ($gadm != null) {
                    $update[] = $gadm;
                }
            }
        }

        return $update;
    }

    protected function remapStates($states)
    {
        $update = [];
        foreach ($states as $state) {
            if (! empty($state)) {
                $gadm = $this->findState($state);
                if ($gadm != null) {
                    $update[] = $gadm;
                }
            }
        }

        return $update;
    }

    protected $_countries;

    protected function findCountry($country)
    {
        if ($this->_countries == null) {
            $this->info('Fetching GADM Level 0 definitions...');
            $this->_countries = $this->runDataQuery(
                <<<SQL
SELECT country AS name, gid_0 AS iso 
FROM gadm_administrative_boundaries 
WHERE adm_level = '0' 
    AND gid_0 NOT IN ('Z01', 'Z02', 'Z03', 'Z04', 'Z05', 'Z06', 'Z07', 'Z08', 'Z09', 'TWN', 'XCA', 'ESH', 'XSP')
SQL
            );
        }

        $name = config("wri.location-mapping.countries.$country.title");
        if (empty($name)) {
            return null;
        }

        $gadm = data_get($this->_countries->firstWhere('name', $name), 'iso');
        if (empty($gadm)) {
            $this->error("Country with name not found in GADM definitions: $name");

            return null;
        }

        return $gadm;
    }

    protected $_states = [];

    protected function findState($state)
    {
        $name = config("wri.location-mapping.states.$state.title");
        if (! empty($name)) {
            $countryCode = $this->findCountry(config("wri.location-mapping.states.$state.country-code"));
        } else {
            // Some free-form entries can be trivially found
            $definition = collect(config('wri.location-mapping.states'))->values()->firstWhere('title', Str::replace('-', ' ', Str::title($state)));

            if (empty($definition)) {
                return null;
            }

            $name = $definition['title'];
            $countryCode = $this->findCountry($definition['country-code']);
        }

        if (data_get($this->_states, $countryCode) == null) {
            $this->info("Fetching GADM Level 1 definitions for $countryCode...");
            data_set($this->_states, $countryCode, $this->runDataQuery(
                <<<SQL
SELECT name_1 AS name, gid_1 AS id 
FROM gadm_administrative_boundaries 
WHERE adm_level='1' AND gid_0 = '$countryCode' 
SQL
            ));
        }

        $gadm = data_get($this->_states[$countryCode]->firstWhere('name', $name), 'id');
        if (empty($gadm)) {
            $this->error("State with name not found in GADM definitions: $name");

            return null;
        }

        return $gadm;
    }

    protected function runDataQuery($sql)
    {
        $response = Http::withHeaders([
            'x-api-key' => self::API_KEY,
            'Origin' => 'terramatch.org',
        ])->acceptJson()->get(self::DATA_API, ['sql' => $sql]);

        return collect(data_get($response->json(), 'data'));
    }
}
