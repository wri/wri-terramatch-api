<?php

namespace App\Http\Resources\V2\FinancialReports;

use Illuminate\Http\Resources\Json\JsonResource;

class FinancialReportLiteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->organisation->name,
            'status' => $this->status,
            'year_of_report' => $this->year_of_report,
            'due_at' => $this->due_at,
            'update_request_status' => $this->update_request_status,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
