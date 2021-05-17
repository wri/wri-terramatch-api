<?php

namespace App\Resources;

use App\Models\Monitoring as MonitoringModel;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Carbon;

class MonitoringResource extends Resource
{
    public function __construct(MonitoringModel $monitoring, ?Carbon $latestProgressUpdateCreatedAt = null)
    {
        $this->id = $monitoring->id;
        $this->match_id = $monitoring->match_id;
        $this->initiator = $monitoring->initiator;
        $this->stage = $monitoring->stage;
        $this->negotiating = $monitoring->negotiating;
        $this->created_by = $monitoring->created_by;
        $this->created_at = $monitoring->created_at;
        $this->pitch = new PitchResource(
            $monitoring->match->interest->pitch,
            $monitoring->match->interest->pitch->approved_version
        );
        $this->offer = new OfferResource(
            $monitoring->match->interest->offer
        );
        $this->updated_at = $latestProgressUpdateCreatedAt;
    }
}