<?php

namespace App\Http\Resources\V2\NurseryReports;

use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NurseryReportsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => NurseryReportLiteResource::collection($this->collection)];
    }

    public function paginationInformation($request, $paginated, $default)
    {
        $default['meta']['unfiltered_total'] = NurseryReport::count();

        return $default;
    }
}
