<?php

namespace App\Http\Resources\V2\SiteReports;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SiteReportsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => SiteReportLiteResource::collection($this->collection)];
    }
}
