<?php

namespace App\Http\Resources\V2\FinancialReports;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FinancialReportsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => FinancialReportLiteResource::collection($this->collection)];
    }
}
