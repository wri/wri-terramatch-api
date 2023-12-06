<?php

namespace App\Resources\Terrafund;

use App\Http\Resources\V2\BaselineMonitoring\SiteMetricResource;
use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundSite as TerrafundSiteModel;
use App\Resources\Resource;

class TerrafundSiteResource extends Resource
{
    public function __construct(TerrafundSiteModel $site)
    {
        $nextDue = $this->getNextDueSubmission($site);

        $this->id = $site->id;
        $this->name = $site->name;
        $this->start_date = $site->start_date;
        $this->end_date = $site->end_date;
        $this->project_country = $site->terrafundProgramme->project_country;
        $this->boundary_geojson = $site->boundary_geojson;
        $this->restoration_methods = $site->restoration_methods;
        $this->land_tenures = $site->land_tenures;
        $this->hectares_to_restore = $site->hectares_to_restore;
        $this->landscape_community_contribution = $site->landscape_community_contribution;
        $this->disturbances = $site->disturbances;
        $this->photos = $this->getPhotos($site);
        $this->terrafund_programme_id = $site->terrafund_programme_id;
        $this->submissions = $this->getSubmissions($site);
        $this->next_due_submission_id = $nextDue->id ?? null;
        $this->next_due_submission_due_at = $nextDue->due_at ?? null;
        $this->created_at = $site->created_at;
        $this->updated_at = $site->updated_at;
        $this->baseline_monitoring = $this->getBaselineMonitoring($site);
    }

    private function getSubmissions($site)
    {
        $resources = [];
        foreach ($site->terrafundSiteSubmissions as $terrafundSiteSubmission) {
            $resources[] = new TerrafundSiteSubmissionResource($terrafundSiteSubmission);
        }

        return $resources;
    }

    private function getPhotos($site)
    {
        $resources = [];
        foreach ($site->terrafundFiles as $terrafundFile) {
            $resources[] = new TerrafundFileResource($terrafundFile);
        }

        return $resources;
    }

    private function getNextDueSubmission(TerrafundSiteModel $site): ?TerrafundDueSubmission
    {
        $dueSubmission = TerrafundDueSubmission::forTerrafundSite()
            ->where('terrafund_due_submissionable_id', '=', $site->id)
            ->unsubmitted()
            ->orderByDesc('due_at')
            ->get();

        return $dueSubmission->first();
    }

    private function getBaselineMonitoring(TerrafundSiteModel $site): ?SiteMetricResource
    {
        $siteMetric = $site->baselineMonitoring->first();
        if (empty($siteMetric)) {
            return null;
        }

        return new SiteMetricResource($siteMetric);
    }
}
