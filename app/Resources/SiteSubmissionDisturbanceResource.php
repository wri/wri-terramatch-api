<?php

namespace App\Resources;

use App\Models\SiteSubmissionDisturbance as SiteSubmissionDisturbanceModel;

class SiteSubmissionDisturbanceResource extends Resource
{
    public function __construct(SiteSubmissionDisturbanceModel $siteDisturbance)
    {
        $this->id = $siteDisturbance->id;
        $this->site_submission_id = $siteDisturbance->site_submission_id;
        $this->disturbance_type = $siteDisturbance->disturbance_type;
        $this->intensity = $siteDisturbance->intensity;
        $this->extent = $siteDisturbance->extent;
        $this->description = $siteDisturbance->description;
        $this->created_at = $siteDisturbance->created_at;
        $this->updated_at = $siteDisturbance->updated_at;
    }
}
