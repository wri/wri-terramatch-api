<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundNursery as TerrafundNurseryModel;
use App\Resources\Resource;

class TerrafundNurseryResource extends Resource
{
    public function __construct(TerrafundNurseryModel $nursery)
    {
        $nextDue = $this->getNextDueSubmission($nursery);
        $this->id = $nursery->id;
        $this->name = $nursery->name;
        $this->start_date = $nursery->start_date;
        $this->end_date = $nursery->end_date;
        $this->project_country = $nursery->terrafundProgramme->project_country;
        $this->boundary_geojson = $nursery->terrafundProgramme->boundary_geojson;
        $this->seedling_grown = $nursery->seedling_grown;
        $this->planting_contribution = $nursery->planting_contribution;
        $this->nursery_type = $nursery->nursery_type;
        $this->terrafund_programme_id = $nursery->terrafund_programme_id;
        $this->tree_species = $this->getTreeSpecies($nursery);
        $this->photos = $this->getPhotos($nursery);
        $this->submissions = $this->getSubmissions($nursery);
        $this->next_due_submission_id = $nextDue->id ?? null;
        $this->next_due_submission_due_at = $nextDue->due_at ?? null;
        $this->created_at = $nursery->created_at;
        $this->updated_at = $nursery->updated_at;
    }

    private function getSubmissions($nursery)
    {
        $resources = [];
        foreach ($nursery->terrafundNurserySubmissions as $terrafundNurserySubmission) {
            $resources[] = new TerrafundNurserySubmissionResource($terrafundNurserySubmission);
        }

        return $resources;
    }

    private function getTreeSpecies($nursery)
    {
        $resources = [];
        foreach ($nursery->terrafundTreeSpecies as $terrafundTreeSpecies) {
            $resources[] = new TerrafundTreeSpeciesResource($terrafundTreeSpecies);
        }

        return $resources;
    }

    private function getPhotos($nursery)
    {
        $resources = [];
        foreach ($nursery->terrafundFiles as $terrafundFile) {
            $resources[] = new TerrafundFileResource($terrafundFile);
        }

        return $resources;
    }

    private function getNextDueSubmission(TerrafundNurseryModel $nursery): ?TerrafundDueSubmission
    {
        $dueSubmission = TerrafundDueSubmission::forTerrafundNursery()
            ->where('terrafund_due_submissionable_id', '=', $nursery->id)
            ->unsubmitted()
            ->orderByDesc('due_at')
            ->get();

        return $dueSubmission->first();
    }
}
