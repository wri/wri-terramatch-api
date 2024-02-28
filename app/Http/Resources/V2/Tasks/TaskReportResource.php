<?php

namespace App\Http\Resources\V2\Tasks;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'due_at' => $this->due_at,
            'report_title' => $this->report_title,
            'update_at' => $this->updated_at,
            'status' => $this->status,
            'nothing_to_report' => $this->nothing_to_report,
            'title' => $this->title,
            'type' => $this->type,
            'parent_name' => $this->parent_name,
            'completion' => $this->completion,
        ];
    }
}
