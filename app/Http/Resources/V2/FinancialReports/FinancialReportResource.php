<?php

namespace App\Http\Resources\V2\FinancialReports;

use App\Http\Resources\V2\FinancialIndicatorsResource;
use App\Http\Resources\V2\Organisation\OrganisationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialReportResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'due_at' => $this->due_at,
            'status' => $this->status,
            'update_request_status' => $this->update_request_status,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'nothing_to_report' => $this->nothing_to_report,
            'submitted_at' => $this->submitted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'financialCollection' => FinancialIndicatorsResource::collection($this->financialCollection),
            'organisation' => new OrganisationResource($this->organisation),
        ];

        return $this->appendFilesToResource($data);
    }
}
