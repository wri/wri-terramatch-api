<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Leaderships;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\Models\V2\User;
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

        $this->info("\n\nUpdating UpdateRequests ...");
        // Find all active form questions that could contain country or state data in update request content.
        $formQuestions = FormQuestion::whereIn('linked_field_key', ['pro-country', 'pro-states'])->get();
        foreach ($formQuestions as $formQuestion) {
            $updateRequests = UpdateRequest::where('content', 'like', "%$formQuestion->uuid%")->whereNot('status', 'approved')->get();
            if ($updateRequests->count() > 0) {
                $this->info("\n\nFixing unapproved update request content for [$formQuestion->linked_field_key, $formQuestion->uuid]");
                $this->withProgressBar($updateRequests->count(), function ($progressBar) use ($formQuestion, $updateRequests) {
                    foreach ($updateRequests as $updateRequest) {
                        $content = $updateRequest->content;
                        $current = data_get($content, $formQuestion->uuid);
                        if ($formQuestion->linked_field_key === 'pro-country') {
                            $country = $this->findCountry($current);
                            data_set($content, $formQuestion->uuid, $country);
                        } else {
                            $states = collect($current)->filter()->map(
                                fn ($state) => $this->findState($state)
                            )->filter()->toArray();
                            data_set($content, $formQuestion->uuid, $states);
                        }

                        $updateRequest->update(['content' => $content]);
                        $progressBar->advance();
                    }
                });
            }
        }

        $this->info("\n\nRemoving `level_2_past_restoration` and `level_2_proposed` data...");
        Organisation::whereNot('level_2_past_restoration', null)->update(['level_2_past_restoration' => null]);
        ProjectPitch::whereNot('level_2_proposed', null)->update(['level_2_proposed' => null]);

        $this->info("\n\nUpdating form question option lists...");
        FormQuestion::where(['options_list' => 'countries', 'input_type' => 'select'])->update(['options_list' => 'gadm-level-0']);
        FormQuestion::where(['options_list' => 'states', 'input_type' => 'select'])->update(['options_list' => 'gadm-level-1']);
        FormQuestion::whereIn('linked_field_key', ['org-level-2-past-restoration', 'pro-pit-level-2-proposed'])
            ->update(['input_type' => 'select', 'multichoice' => true, 'options_list' => 'gadm-level-2']);

        $this->info("\n\nUpdating user countries...");
        foreach (User::whereNotNull('country')->get() as $user) {
            $user->update(['country' => $this->findCountry($user->country)]);
        }

        $this->info("\n\nUpdating leadership nationalities...");
        foreach (Leaderships::whereNotNull('nationality')->get() as $leadership) {
            $leadership->update(['nationality' => $this->findCountry($leadership->nationality)]);
        }
    }

    protected function handleMigrationSet($query, $definitions)
    {
        $count = (clone $query)->count();
        $this->withProgressBar($count, function ($progressBar) use ($query, $definitions) {
            $query->chunk(100, function ($chunk) use ($progressBar, $definitions) {
                foreach ($chunk as $entity) {
                    foreach ($definitions as $type => $columns) {
                    $isSingle = Str::endsWith($type, '_single');
                    $isCountry = Str::startsWith($type, 'gadm_0');

                    foreach ($columns as $column) {
                        $values = $isSingle ? [$entity[$column]] : $entity[$column];
                        
                        $values = collect($values)
                            ->map(fn ($v) => $isCountry ? $this->findCountry($v) : $this->findState($v))
                            ->filter()
                            ->toArray();

                        $entity[$column] = $isSingle ? data_get($values, 0) : $values;
                    }

                    $entity->save();
                    $progressBar->advance();
                }
            });
        });
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
