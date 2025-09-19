<?php

namespace App\Http\Resources\V2\DisturbanceReports;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DisturbanceReportsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => DisturbanceReportLiteResource::collection($this->collection)];
    }
}
