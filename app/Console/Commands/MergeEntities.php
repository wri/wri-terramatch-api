<?php

namespace App\Console\Commands;

use App\Models\V2\EntityModel;
use App\Models\V2\MediaModel;
use App\Models\V2\Sites\Site;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MergeEntities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merge-entities 
         {type : The type of entity that is being merged. Supported types: sites}
         {merged : The UUID of the base (merged) entity} 
         {feeders* : The UUIDS of the feeder entities}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merges entities from a set of feeder entities into a base "merged" entity.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        switch ($type) {
            case 'sites':
                $entities = $this->getEntities(Site::class);
                $merged = $entities->shift();
                $this->mergeSites($merged, $entities);

                break;

            default:
                $this->abort("Unsupported type: $type");
        }
    }

    private function getEntities($modelClass): Collection
    {
        $mergedUuid = $this->argument('merged');
        $merged = $modelClass::isUuid($mergedUuid)->first();
        if ($merged == null) {
            $this->abort("Base model not found: $mergedUuid");
        }

        $feederUuids = $this->argument('feeders');
        $feeders = Site::whereIn('uuid', $feederUuids)->get();
        if (count($feeders) != count($feederUuids)) {
            $this->abort('Some feeders not found: ' . json_encode($feederUuids));
        }

        return collect([$merged])->push($feeders)->flatten();
    }

    #[NoReturn] private function abort(string $message, int $exitCode = 1): void
    {
        echo $message;
        exit($exitCode);
    }

    private function confirmMerge(string $mergeName, Collection $feederNames): void
    {
        $mergeMessage = "Would you like to execute this merge? This operation cannot easily be undone...\n".
            "  Merged Entity Name:\n    $mergeName\n" .
            "  Feeder Entity Names: \n    " .
            $feederNames->join("\n    ")
            . "\n\n";
        if (!$this->confirm($mergeMessage)) {
            $this->abort('Merge aborted', 0);
        }
    }

    private function mergeSites(Site $mergeSite, Collection $feederSites): void
    {
        $frameworks = $feederSites->map(fn (Site $site) => $site->framework_key)->push($mergeSite->framework_key)->unique();
        if ($frameworks->count() > 1) {
            $this->abort('Multiple frameworks detected in sites: ' . json_encode($frameworks));
        }

        $projectIds = $feederSites->map(fn (Site $site) => $site->project_id)->push($mergeSite->project_id)->unique();
        if ($projectIds->count() > 1) {
            $this->abort('Multiple project_ids detected in sites: ' . json_encode($projectIds));
        }

        $this->confirmMerge($mergeSite->name, $feederSites->map(fn ($site) => $site->name));

        try {
            DB::beginTransaction();
            $this->mergeEntities($mergeSite, $feederSites);

            // merge report information from the same reporting period (should be on the same task) and remove all update requests

            // remove all outstanding update requests

            // remove all feeder entities

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $this->abort("Exception encountered during merge operation, transaction aborted: " . $e->getMessage());
        }
    }

    /**
     * Merges entity information and remove all update requests. Merged entity will be in 'awaiting-approval' state
     * @throws Exception
     */
    private function mergeEntities(EntityModel $merge, Collection $feeders): void
    {
        $config = config("wri.entity-merge-mapping.models.$merge->shortName.frameworks.$merge->framework_key");
        if (empty($config)) {
            throw new Exception("Merge mapping configuration not found: $merge->shortName, $merge->framework_key");
        }

        $entities = collect([$merge])->push($feeders)->flatten();
        foreach ($config['properties'] ?? [] as $property => $commandSpec) {
            $commandParts = explode(':', $commandSpec);
            $command = array_shift($commandParts);
            switch ($command) {
                case 'date':
                    $dates = $entities->map(fn ($entity) => Carbon::parse($entity->$property));
                    $merge->$property = $this->mergeDates($dates, ...$commandParts);
                    break;

                case 'long-text':
                    $texts = $entities->map(fn ($entity) => $entity->$property);
                    $merge->$property = $texts->join("\n\n");
                    break;

                case 'set-null':
                    $merge->$property = null;
                    break;

                case 'union':
                    $sets = $entities->map(fn ($entity) => $entity->$property);
                    $merge->$property = $sets->flatten()->filter()->unique()->all();
                    break;

                case 'sum':
                    $values = $entities->map(fn ($entity) => $entity->$property);
                    $merge->$property = $values->sum();
                    break;

                default:
                    throw new Exception("Unknown properties command: $command");
            }
        }

        foreach ($config['relations'] ?? [] as $property => $commandSpec) {
            $commandParts = explode(':', $commandSpec);
            $command = array_shift($commandParts);
            switch ($command) {
                case 'move-to-merged':
                    $this->moveAssociations($property, $merge, $feeders);
                    break;

                default:
                    throw new Exception("Unknown relations command: $command");
            }
        }

        foreach ($config['file-collections'] ?? [] as $property => $commandSpec) {
            $commandParts = explode(':', $commandSpec);
            $command = array_shift($commandParts);
            switch ($command) {
                case 'move-to-merged':
                    /** @var MediaModel $merge */
                    $this->moveMedia($property, $merge, $feeders);
                    break;

                default:
                    throw new Exception("Unknown file collections command: $command");
            }
        }
    }

    /**
     * @throws Exception
     */
    private function mergeDates(Collection $dates, $strategy): Carbon
    {
        return $dates->reduce(function (?Carbon $carry, Carbon $date) use ($strategy) {
            if ($carry == null) return $date;

            return match ($strategy) {
                'first' => $carry->minimum($date),
                'last' => $carry->maximum($date),
                default => throw new Exception("Unrecognized date strategy: $strategy"),
            };
        });
    }

    private function moveAssociations(string $property, EntityModel $merge, Collection $feeders): void
    {
        // In this method we assume that the type of $merge and the models in $feeders match, so we simply
        // need to update the foreign key for each of the associated models (and can ignore the type). We expect the
        // relationship to be a MorphMany

        $foreignKey = $merge->$property()->getForeignKeyName();
        foreach ($feeders as $feeder) {
            $feeder->$property()->update([$foreignKey => $merge->id]);
        }
    }

    private function moveMedia(string $collection, MediaModel $merge, Collection $feeders): void
    {
        /** @var MediaModel $feeder */
        foreach ($feeders as $feeder) {
            /** @var Media $media */
            foreach ($feeder->getMedia($collection) as $media) {
                $media->move($merge, $collection);
            }
        }
    }
}
