<?php

namespace App\Observers;

use App\Jobs\NotifyGreenhouseJob;
use App\Models\V2\Sites\SitePolygon;

class SitePolygonObserver
{
    public function updated(SitePolygon $sitePolygon): void
    {
        if ($this->createdByGreenhouse($sitePolygon)) {
            $this->notifyGreenhouse($sitePolygon->uuid);
        }
    }

    public function deleting(SitePolygon $sitePolygon): void
    {
        if ($this->createdByGreenhouse($sitePolygon)) {
            $this->notifyGreenhouse($sitePolygon->uuid);
        }
    }

    protected function createdByGreenhouse(SitePolygon $sitePolygon): bool
    {
        return $sitePolygon->createdBy()->first()?->primaryRole?->name == 'greenhouse-service-account';
    }

    protected function notifyGreenhouse(string $uuid): void
    {
        NotifyGreenhouseJob::dispatch('notifyPolygonUpdated', $uuid);
    }
}
