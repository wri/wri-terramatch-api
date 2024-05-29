<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Abortable;
use App\Models\V2\EntityModel;
use App\Models\V2\MediaModel;
use App\Models\V2\ReportModel;
use App\Models\V2\Sites\Site;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\StateMachines\EntityStatusStateMachine;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MergeEntities extends Command
{
    use Abortable;

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
        $this->assert($merged != null, "Base model not found: $mergedUuid");

        $feederUuids = $this->argument('feeders');
        // This would be faster as a whereIn, but we want to keep the order intact; matching it with the
        // order that was passed into the command
        $feeders = collect($feederUuids)->map(fn ($uuid) => $modelClass::isUuid($uuid)->first());
        $this->assert(
            count($feeders) == count($feederUuids),
            'Some feeders not found: ' . json_encode($feederUuids)
        );

        return collect([$merged])->push($feeders)->flatten();
    }

    private function confirmMerge(string $mergeName, Collection $feederNames): void
    {
        $mergeMessage = "Would you like to execute this merge? This operation cannot easily be undone...\n".
            "  Merged Entity Name:\n    $mergeName\n" .
            "  Feeder Entity Names: \n    " .
            $feederNames->join("\n    ")
            . "\n\n";
        $this->assert($this->confirm($mergeMessage), 'Merge aborted', 0);
    }

    // Note for future expansion, the code to merge nurseries would be basically the same as this, but this pattern
    // wouldn't work for projects because it relies on ensuring that the parent entity (the project for sites/nurseries)
    // is the same, and projects would need to dig into merging their sites and nurseries as well.
    private function mergeSites(Site $mergeSite, Collection $feederSites): void
    {
        $frameworks = $feederSites->map(fn (Site $site) => $site->framework_key)->push($mergeSite->framework_key)->unique();
        $this->assert($frameworks->count() <= 1, 'Multiple frameworks detected in sites: ' . json_encode($frameworks));

        $projectIds = $feederSites->map(fn (Site $site) => $site->project_id)->push($mergeSite->project_id)->unique();
        $this->assert($projectIds->count() <= 1, 'Multiple project_ids detected in sites: ' . json_encode($projectIds));

        $this->confirmMerge($mergeSite->name, $feederSites->map(fn ($site) => $site->name));

        try {
            DB::beginTransaction();

            $this->mergeEntities($mergeSite, $feederSites);
            $this->mergeReports($mergeSite, $feederSites);
            $feederSites->each(function ($site) { $site->delete(); });

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $this->abort('Exception encountered during merge operation, transaction aborted: ' . $e->getMessage() . "\n\n");
        }

        echo "Merge complete!\n\n";
    }

    /**
     * Merges all reports from the feeder entities into the merge entity's reports. Finds associated reporting
     * periods through the task associated with the merge entity's reports. The feeder's reports are removed and the
     * Merge reports are put in the 'awaiting-approval' state. All associated update requests are removed.
     * @throws Exception
     */
    private function mergeReports(EntityModel $merge, Collection $feeders): void
    {
        /** @var ReportModel $report */
        $foreignKey = $merge->reports()->getForeignKeyName();
        foreach ($merge->reports()->get() as $report) {
            $hasMany = $report->task->hasMany(get_class($report));
            // A whereIn would be faster, but we want to keep the reports in the same order as the feeders
            $associatedReports = $feeders->map(fn ($feeder) => (clone $hasMany)->where($foreignKey, $feeder->id)->first())->filter();
            $this->mergeEntities($report, $associatedReports);
            $associatedReports->each(function ($report) { $report->delete(); });
        }
    }

    /**
     * Merges entity information and remove all update requests. Merged entity will be in 'awaiting-approval' state.
     * The caller is responsible for removing the feeder entities.
     * @throws Exception
     */
    private function mergeEntities(EntityModel $merge, Collection $feeders): void
    {
        $config = config("wri.entity-merge-mapping.models.$merge->shortName.frameworks.$merge->framework_key");
        if (empty($config)) {
            throw new Exception("Merge mapping configuration not found: $merge->shortName, $merge->framework_key");
        }

        $this->processProperties(data_get($config, 'properties'), $merge, $feeders);
        $this->processRelations(data_get($config, 'relations'), $merge, $feeders);
        $this->processFileCollections(data_get($config, 'file-collections'), $merge, $feeders);

        // Conditionals has to come after the other sets because it relies on the data for the above being accurate. We
        // also want to make sure none of the relations are cached with incorrect values, so a save and refresh is
        // appropriate here.
        $merge->save();
        $merge->refresh();
        $this->processConditionals(data_get($config, 'conditionals'), $merge, $feeders);


        $merge->save();
        $merge->updateRequests()->delete();
        $feeders->each(function ($feeder) { $feeder->updateRequests()->delete(); });
        $merge->update([
            'answers' => $merge->getEntityAnswers($merge->getForm()),
            'status' => EntityStatusStateMachine::AWAITING_APPROVAL,
            'update_request_status' => UpdateRequest::ENTITY_STATUS_NO_UPDATE,
        ]);
    }

    /**
     * @throws Exception
     */
    private function processProperties($properties, $merge, $feeders): void
    {
        $entities = collect([$merge])->push($feeders)->flatten();
        foreach ($properties ?? [] as $property => $commandSpec) {
            $commandParts = explode(':', $commandSpec);
            $command = array_shift($commandParts);
            switch ($command) {
                case 'date':
                    $dates = $entities
                        ->map(fn ($entity) => empty($entity->$property) ? null : Carbon::parse($entity->$property))
                        ->filter();
                    $merge->$property = $this->mergeDates($dates, ...$commandParts);

                    break;

                case 'long-text':
                    $texts = $entities->map(fn ($entity) => $entity->$property)->filter();
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

                case 'ensure-unique-string':
                    $texts = $entities->map(fn ($entity) => $entity->$property);
                    $merge->$property = $this->ensureUniqueString($property, $texts);

                    break;

                default:
                    throw new Exception("Unknown properties command: $command");
            }
        }

        // Make sure any property changes don't get wiped out be a refresh() further down the migration process.
        $merge->save();
    }

    /**
     * @throws Exception
     */
    private function processRelations($relations, $merge, $feeders): void
    {
        foreach ($relations ?? [] as $property => $commandSpec) {
            $commandParts = explode(':', $commandSpec);
            $command = array_shift($commandParts);
            switch ($command) {
                case 'move-to-merged':
                    $this->moveAssociations($property, $merge, $feeders);

                    break;

                case 'tree-species-merge':
                    $this->treeSpeciesMerge($property, $merge, $feeders);

                    break;

                default:
                    throw new Exception("Unknown relations command: $command");
            }
        }
    }

    /**
     * @throws Exception
     */
    private function processConditionals($conditionals, $merge, $feeders): void
    {
        // Some of the reports that are merging in are "migrated" models, which means that we can't rely on their
        // answers field as a source of truth. Instead, we set the conditional to true if the field that it hides
        // has any content.
        $answers = [];
        if (! empty($conditionals)) {
            $form = $merge->getForm();
            // get an associative array of uuid -> question for all questions in the form.
            $questions = $form
                ->sections
                ->map(fn ($section) => $section->questions)
                ->flatten()
                ->mapWithKeys(fn ($question) => [$question->uuid => $question]);

            foreach ($conditionals as $linkedField => $commandSpec) {
                $linkedFieldQuestion = $questions->first(fn ($question) => $question->linked_field_key == $linkedField);
                if ($linkedFieldQuestion == null) {
                    throw new Exception("No question found for linked field: $linkedFieldQuestion");
                }
                if (! $linkedFieldQuestion->show_on_parent_condition) {
                    throw new Exception("Question for linked field isn't gated by a conditional: $linkedFieldQuestion");
                }

                $conditional = $questions[$linkedFieldQuestion->parent_id];
                if ($conditional == null) {
                    throw new Exception("No parent conditional found for linked field: $linkedFieldQuestion");
                }
                if ($conditional['input_type'] != 'conditional') {
                    throw new Exception("Parent of linked field question is not a conditional: $linkedFieldQuestion");
                }

                $commandParts = explode(':', $commandSpec);
                $command = array_shift($commandParts);
                switch ($command) {
                    case 'has-relation':
                        $property = $commandParts[0];
                        $answers[$conditional->uuid] = $merge->$property()->count() > 0;

                        break;

                    case 'has-text':
                        $property = $commandParts[0];
                        $answers[$conditional->uuid] = ! empty($merge->$property);

                        break;

                    default:
                        throw new Exception("Unknown conditionals command: $command");
                }
            }
        }
        $merge->answers = $answers;
    }

    /**
     * @throws Exception
     */
    private function processFileCollections($fileCollections, $merge, $feeders): void
    {
        foreach ($fileCollections ?? [] as $collection => $commandSpec) {
            $commandParts = explode(':', $commandSpec);
            $command = array_shift($commandParts);
            switch ($command) {
                case 'move-to-merged':
                    /** @var MediaModel $merge */
                    $this->moveMedia($collection, $merge, $feeders);

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
            if ($carry == null) {
                return $date;
            }

            return match ($strategy) {
                'first' => $carry->minimum($date),
                'last' => $carry->maximum($date),
                default => throw new Exception("Unrecognized date strategy: $strategy"),
            };
        });
    }

    /**
     * @throws Exception
     */
    private function ensureUniqueString(string $property, Collection $texts): ?string
    {
        $unique = $texts->filter()->unique();
        if ($unique->count() == 0) {
            return null;
        }

        if ($unique->count() > 1) {
            throw new Exception("Property required to be unique is not: $property, " . json_encode($unique));
        }

        return $unique->first();
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

    private function treeSpeciesMerge(string $property, EntityModel $merge, Collection $feeders): void
    {
        $foreignKey = $merge->$property()->getForeignKeyName();
        foreach ($feeders as $feeder) {
            /** @var TreeSpecies $feederTree */
            foreach ($feeder->$property()->get() as $feederTree) {
                if ($merge->$property()->where('name', $feederTree->name)->exists()) {
                    /** @var TreeSpecies $baseTree */
                    $baseTree = $merge->$property()->where('name', $feederTree->name)->first();
                    $baseTree->update(['amount' => $baseTree->amount + $feederTree->amount]);
                    $feederTree->delete();
                } else {
                    $feederTree->update([$foreignKey => $merge->id]);
                    // Make sure that the merge model's association is aware of the addition
                    $merge->refresh();
                }
            }
        }
    }

    private function moveMedia(string $collection, MediaModel $merge, Collection $feeders): void
    {
        /** @var MediaModel $feeder */
        foreach ($feeders as $feeder) {
            /** @var Media $media */
            foreach ($feeder->getMedia($collection) as $media) {
                // Spatie as a "move" method, but it tries to download, copy, upload and then remove the original media.
                // It appears to be kosher for us to just move the DB association, which is both faster and testable on
                // staging.
                $media->update(['model_id' => $merge->id]);
            }
        }
    }
}
