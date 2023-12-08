<?php

namespace App\Http\Resources\V2\ProjectReports;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectReportsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => ProjectReportLiteResource::collection($this->collection)];
    }
}
