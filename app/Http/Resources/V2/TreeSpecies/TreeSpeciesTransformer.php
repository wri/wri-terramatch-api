<?php

namespace App\Http\Resources\V2\TreeSpecies;

use App\Models\V2\EntityModel;
use App\Models\V2\Projects\Project;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class TreeSpeciesTransformer
{
    private Project $project;
    private Collection $projectTreeSpecies;
    private SupportCollection $siteReportTreeSpecies;

    public function __construct(EntityModel $entity, Collection $projectTreeSpecies)
    {
        if (!($entity instanceof Project)) {
            throw new \InvalidArgumentException('Entity must be an instance of Project');
        }

        $this->project = $entity;
        $this->projectTreeSpecies = $projectTreeSpecies;
        $this->siteReportTreeSpecies = $this->getSiteReportTreeSpecies();
    }

    public function transform(): Collection
    {
        $this->projectTreeSpecies->each(function ($species) {
            $identifier = $species->taxon_id ?? $species->name;
            $reportAmount = $this->getReportAmount($identifier);
            
            $species->report_amount = $reportAmount;
            $species->is_new_species = false;
        });

        $newSpecies = $this->getNewSpecies();
        
        return new Collection(
            $this->projectTreeSpecies->concat($newSpecies)
        );
    }

    private function getSiteReportTreeSpecies(): SupportCollection
    {
        $ids = $this->project->submittedSiteReportIds()->pluck('id');
        return TreeSpecies::whereIn('speciesable_id',$ids)
            ->where('collection', 'tree-planted')
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
            
            $existsInProject = $this->projectTreeSpecies->contains(function ($species) use ($identifier) {
                return ($species->taxon_id ?? $species->name) === $identifier;
            });

            if (!$existsInProject) {
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