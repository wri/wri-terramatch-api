<?php

namespace App\Resources;

use App\Models\Target as TargetModel;

class TargetResource extends Resource
{
    public function __construct(TargetModel $target)
    {
        $this->id = $target->id;
        $this->monitoring_id = $target->monitoring_id;
        $this->negotiator = $target->negotiator;
        $this->start_date = $target->start_date;
        $this->finish_date = $target->finish_date;
        $this->funding_amount = $target->funding_amount;
        $this->land_geojson = $target->land_geojson;
        $this->data = (object) $target->data;
        $this->created_at = $target->created_at;
        $this->created_by = $target->created_by;
        $this->updated_at = $target->updated_at;
        $this->accepted_at = $target->accepted_at;
        $this->accepted_by = $target->accepted_by;
    }
}
