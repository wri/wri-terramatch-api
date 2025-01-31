<?php

namespace App\Console\Commands\Migration;

use App\Models\Programme;
use App\Models\ProgrammeTreeSpecies;
use App\Models\Site as PPCSite;
use App\Models\SiteSubmission;
use App\Models\SiteTreeSpecies;
use App\Models\Submission;
use App\Models\Terrafund\TerrafundNoneTreeSpecies;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\Terrafund\TerrafundTreeSpecies;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Console\Command;

class TreeSpeciesMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:tree-species {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Tree Species Data only to  V2 tables';

    protected $modelMap = [
        TerrafundSiteSubmission::class => SiteReport::class,
        TerrafundNursery::class => Nursery::class,
    ];

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            TreeSpecies::truncate();
        }

        $collection = SiteTreeSpecies::all();
        foreach ($collection as $species) {
            $count++;
            $map = $this->mapSiteTreeSpeciesValues($species);
            if (is_array($map)) {
                $new = TreeSpecies::create($map);
                $created++;

                if ($this->option('timestamps')) {
                    $new->created_at = $species->created_at;
                    $new->updated_at = $species->updated_at;
                    $new->save();
                }
            }
        }

        $collection = ProgrammeTreeSpecies::all();
        foreach ($collection as $species) {
            $count++;
            $map = $this->mapProgrammeTreeSpeciesValues($species);
            if (is_array($map)) {
                $new = TreeSpecies::create($map);
                $created++;

                if ($this->option('timestamps')) {
                    $new->created_at = $species->created_at;
                    $new->updated_at = $species->updated_at;
                    $new->save();
                }
            }
        }

        $collection = TerrafundTreeSpecies::all();
        foreach ($collection as $species) {
            $count++;
            $map = $this->mapTerrafundTreeSpeciesValues($species);
            if (is_array($map)) {
                $new = TreeSpecies::create($map);
                $created++;

                if ($this->option('timestamps')) {
                    $new->created_at = $species->created_at;
                    $new->updated_at = $species->updated_at;
                    $new->save();
                }
            }
        }

        $collection = TerrafundNoneTreeSpecies::all();
        foreach ($collection as $species) {
            $count++;
            $map = $this->mapTerrafundNoneTreeSpeciesValues($species);
            if (is_array($map)) {
                $new = TreeSpecies::create($map);
                $created++;

                if ($this->option('timestamps')) {
                    $new->created_at = $species->created_at;
                    $new->updated_at = $species->updated_at;
                    $new->save();
                }
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapTerrafundNoneTreeSpeciesValues(TerrafundNoneTreeSpecies $species): ?array
    {
        $modelName = $this->modelMap[$species->speciesable_type];
        $model = app($modelName);
        $item = $model::where('old_model', $species->speciesable_type)
            ->where('old_id', $species->speciesable_id)
            ->first();

        if (! empty($item)) {
            return [
                'old_model' => TerrafundNoneTreeSpecies::class,
                'old_id' => $species->id,
                'collection' => $modelName == Nursery::class ? TreeSpecies::COLLECTION_NURSERY : TreeSpecies::COLLECTION_NON_TREE,

                'name' => data_get($species, 'name'),
                'amount' => data_get($species, 'amount'),
                'speciesable_id' => $item->id,
                'speciesable_type' => $this->modelMap[$species->speciesable_type],
            ];
        }

        return null;
    }

    private function mapTerrafundTreeSpeciesValues(TerrafundTreeSpecies $species): ?array
    {
        $modelName = $this->modelMap[$species->treeable_type];
        $model = app($modelName);
        $item = $model::where('old_model', $species->treeable_type)
            ->where('old_id', $species->treeable_id)
            ->first();

        if (! empty($item)) {
            return [
                'old_model' => TerrafundTreeSpecies::class,
                'old_id' => $species->id,
                'collection' => $modelName == Nursery::class ? TreeSpecies::COLLECTION_NURSERY : TreeSpecies::COLLECTION_PLANTED,

                'name' => data_get($species, 'name'),
                'amount' => data_get($species, 'amount'),
                'speciesable_id' => $item->id,
                'speciesable_type' => $this->modelMap[$species->treeable_type],
            ];
        }

        return null;
    }

    private function mapProgrammeTreeSpeciesValues(ProgrammeTreeSpecies $species): ?array
    {
        if (empty($species->programme_submission_id)) {
            $project = Project::where('old_model', Programme::class)
                ->where('old_id', $species->programme_id)
                ->first();

            if (! empty($project)) {
                return [
                    'old_model' => ProgrammeTreeSpecies::class,
                    'old_id' => $species->id,
                    'collection' => TreeSpecies::COLLECTION_PLANTED,

                    'name' => data_get($species, 'name'),
                    'amount' => data_get($species, 'amount'),
                    'speciesable_id' => $project->id,
                    'speciesable_type' => Project::class,
                ];
            }
        } else {
            $report = ProjectReport::where('old_model', Submission::class)
                ->where('old_id', $species->programme_submission_id)
                ->first();

            if (! empty($report)) {
                return [
                    'old_model' => ProgrammeTreeSpecies::class,
                    'old_id' => $species->id,
                    'collection' => TreeSpecies::COLLECTION_PLANTED,

                    'name' => data_get($species, 'name'),
                    'amount' => data_get($species, 'amount'),
                    'speciesable_id' => $report->id,
                    'speciesable_type' => ProjectReport::class,
                ];
            }
        }

        return null;
    }

    private function mapSiteTreeSpeciesValues(SiteTreeSpecies $species): ?array
    {
        if (! empty($species->site_submission_id)) {
            $submission = SiteReport::where('old_model', SiteSubmission::class)
                ->where('old_id', $species->site_submission_id)
                ->first();

            if (! empty($submission)) {
                return [
                    'old_model' => SiteTreeSpecies::class,
                    'old_id' => $species->id,
                    'collection' => TreeSpecies::COLLECTION_PLANTED,

                    'name' => data_get($species, 'name'),
                    'amount' => data_get($species, 'amount'),
                    'speciesable_id' => $submission->id,
                    'speciesable_type' => SiteReport::class,
                ];
            }
        } elseif (! empty($species->site_id)) {
            $site = Site::where('old_model', PPCSite::class)
                ->where('old_id', $species->site_id)
                ->first();

            if (! empty($site)) {
                return [
                    'old_model' => SiteTreeSpecies::class,
                    'old_id' => $species->id,
                    'collection' => TreeSpecies::COLLECTION_PLANTED,

                    'name' => data_get($species, 'name'),
                    'amount' => data_get($species, 'amount'),
                    'speciesable_id' => $site->id,
                    'speciesable_type' => Site::class,
                ];
            }
        }

        return null;
    }
}
