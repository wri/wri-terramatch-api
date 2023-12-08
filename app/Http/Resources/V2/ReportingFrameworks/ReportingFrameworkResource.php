<?php

namespace App\Http\Resources\V2\ReportingFrameworks;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportingFrameworkResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'access_code' => $this->access_code,
            'project_form_uuid' => $this->project_form_uuid,
            'project_report_form_uuid' => $this->project_report_form_uuid,
            'site_form_uuid' => $this->site_form_uuid,
            'site_report_form_uuid' => $this->site_report_form_uuid,
            'nursery_form_uuid' => $this->nursery_form_uuid,
            'nursery_report_form_uuid' => $this->nursery_report_form_uuid,
            'total_projects_count' => $this->total_projects_count,
        ];
    }
}
