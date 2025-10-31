<?php

namespace App\Http\Resources\V2\SrpReports;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SrpReportsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => SrpReportLiteResource::collection($this->collection)];
    }
}
