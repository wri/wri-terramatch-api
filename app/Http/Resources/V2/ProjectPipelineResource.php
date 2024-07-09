<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectPipelineResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
            ],
            'date' => $this->updated_at,
            'id' => $this->id,
            'submitted_by' => ($this->submittedBy->first_name ?? '') . ' ' . ($this->submittedBy->last_name ?? ''),
            'program' => $this->program,
            'cohort' => $this->cohort,
            'publish_for' => $this->publish_for,
            'url' => $this->url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
