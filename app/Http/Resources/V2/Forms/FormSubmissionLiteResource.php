<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\JsonResource;

class FormSubmissionLiteResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'updated_by_uuid' => $this->user_id,
            'project_pitch_uuid' => $this->project_pitch_uuid,
            'form_uuid' => $this->form_id,
            'stage' => [
                'uuid' => data_get($this->stage, 'uuid'),
                'name' => data_get($this->stage, 'name'),
                'order' => data_get($this->stage, 'order'),
            ],
        ];
    }
}
