<?php

namespace App\Http\Resources\V2\TreeSpecies;

use App\Models\V2\EntityModel;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class TreeSpeciesTransformer
{
    private EntityModel $entity;

    private Collection $entityTreeSpecies;

    private SupportCollection $siteReportTreeSpecies;

    private string $collectionType;

    public function __construct(EntityModel $entity, Collection $entityTreeSpecies, string $collectionType)
    {
        $validEntityTypes = [Project::class, Site::class, ProjectReport::class, NurseryReport::class];

        if (! in_array(get_class($entity), $validEntityTypes)) {
            throw new \InvalidArgumentException(
                'Entity must be an instance of Project, Site, or ProjectReport'
            );
        }

        $this->entity = $entity;
        $this->entityTreeSpecies = $entityTreeSpecies;
        $this->collectionType = $collectionType;
        $this->siteReportTreeSpecies = $this->getSiteReportTreeSpecies();
    }

    public function transform(): Collection
    {
        if ($this->entity instanceof ProjectReport) {
            return $this->transformProjectReport();
        }

        if ($this->entity instanceof NurseryReport) {
            return $this->transformNurseryReport();
        }

        // For Project and Site entities
        $this->entityTreeSpecies->each(function ($species) {
            $identifier = $species->taxon_id ?? $species->name;
            $reportAmount = $this->getReportAmount($identifier);

            $species->report_amount = $reportAmount;
            $species->is_new_species = false;
        });

        $newSpecies = $this->getNewSpecies();

        $combinedSpecies = $this->entityTreeSpecies->concat($newSpecies);

        return new Collection(
            $combinedSpecies->sortByDesc('report_amount')
        );
    }

    private function transformNurseryReport(): Collection
    {
        if ($this->collectionType !== TreeSpecies::COLLECTION_NURSERY) {
            throw new \InvalidArgumentException(
                'collection should be ' . TreeSpecies::COLLECTION_NURSERY
            );
        }
        $reportTreeSpecies = $this->getNurseryReportTreeSpecies();
        $transformedSpecies = $reportTreeSpecies->map(function ($reportSpecies) {
            $species = new TreeSpecies([
                'name' => $reportSpecies['name'],
                'amount' => $reportSpecies['amount'],
                'collection' => $reportSpecies['collection'],
                'taxon_id' => $reportSpecies['taxon_id'],
            ]);

            $species->report_amount = $reportSpecies['amount'];
            $species->is_new_species = false;

            return $species;
        });

        return new Collection(
            $transformedSpecies->sortByDesc('report_amount')
        );
    }

    private function transformProjectReport(): Collection
    {
        $reportTreeSpecies = $this->collectionType === TreeSpecies::COLLECTION_NURSERY ? $this->getProjectReportTreeSpecies() : $this->siteReportTreeSpecies;
        $transformedSpecies = $reportTreeSpecies->map(function ($reportSpecies) {
            $species = new TreeSpecies([
                'name' => $reportSpecies['name'],
                'amount' => $reportSpecies['amount'],
                'collection' => $reportSpecies['collection'],
                'taxon_id' => $reportSpecies['taxon_id'],
            ]);

            $species->report_amount = $reportSpecies['amount'];
            $species->is_new_species = false;

            return $species;
        });

        return new Collection(
            $transformedSpecies->sortByDesc('report_amount')
        );
    }

    private function getSiteReportTreeSpecies(): SupportCollection
    {
        $ids = $this->getSiteReportIds();

        return TreeSpecies::whereIn('speciesable_id', $ids)
            ->where('speciesable_type', SiteReport::class)
            ->where('collection', $this->collectionType)
            ->where('hidden', false)
            ->get()
            ->groupBy(function ($species) {
                return $species->taxon_id ?? $species->name;
            })
            ->map(function ($group) {
                return [
                    'taxon_id' => $group->first()->taxon_id,
                    'name' => $group->first()->name,
                    'amount' => $group->sum('amount'),
                    'collection' => $group->first()->collection,
                ];
            })
            ->values();
    }

    private function getNurseryReportTreeSpecies(): SupportCollection
    {
        $id = $this->entity->id;

        return TreeSpecies::where('speciesable_id', $id)
            ->where('speciesable_type', NurseryReport::class)
            ->where('collection', TreeSpecies::COLLECTION_NURSERY)
            ->where('hidden', false)
            ->get()
            ->groupBy(function ($species) {
                return $species->taxon_id ?? $species->name;
            })
            ->map(function ($group) {
                return [
                    'taxon_id' => $group->first()->taxon_id,
                    'name' => $group->first()->name,
                    'amount' => $group->sum('amount'),
                    'collection' => $group->first()->collection,
                ];
            })
            ->values();
    }

    private function getProjectReportTreeSpecies(): SupportCollection
    {
        $id = $this->entity->id;

        return TreeSpecies::where('speciesable_id', $id)
            ->where('speciesable_type', ProjectReport::class)
            ->where('collection', TreeSpecies::COLLECTION_NURSERY)
            ->where('hidden', false)
            ->get()
            ->groupBy(function ($species) {
                return $species->taxon_id ?? $species->name;
            })
            ->map(function ($group) {
                return [
                    'taxon_id' => $group->first()->taxon_id,
                    'name' => $group->first()->name,
                    'amount' => $group->sum('amount'),
                    'collection' => $group->first()->collection,
                ];
            })
            ->values();
    }

    private function getSiteReportIds(): array
    {
        return match (true) {
            $this->entity instanceof Project => $this->entity->approvedSiteReportIds()->pluck('id')->toArray(),
            $this->entity instanceof Site => $this->entity->approvedReportIds()->pluck('id')->toArray(),
            $this->entity instanceof ProjectReport => $this->entity->task?->siteReports()->where('status', ReportStatusStateMachine::APPROVED)->pluck('id')->toArray() ?? [],
            default => [],
        };
    }

    private function getReportAmount(string $identifier): int
    {
        return (int) $this->siteReportTreeSpecies
            ->filter(function ($reportSpecies) use ($identifier) {
                return ($reportSpecies['taxon_id'] ?? $reportSpecies['name']) === $identifier;
            })
            ->sum('amount');
    }

    private function getNewSpecies(): Collection
    {
        $newSpecies = new Collection();

        foreach ($this->siteReportTreeSpecies as $reportSpecies) {
            $identifier = $reportSpecies['taxon_id'] ?? $reportSpecies['name'];

            $existsInEntity = $this->entityTreeSpecies->contains(function ($species) use ($identifier) {
                return ($species->taxon_id ?? $species->name) === $identifier;
            });

            if (! $existsInEntity) {
                $species = new TreeSpecies([
                    'name' => $reportSpecies['name'],
                    'amount' => $reportSpecies['amount'],
                    'collection' => $reportSpecies['collection'],
                    'taxon_id' => $reportSpecies['taxon_id'],
                ]);

                $species->report_amount = $reportSpecies['amount'];
                $species->is_new_species = true;

                $newSpecies->push($species);
            }
        }

        return $newSpecies;
    }
}
