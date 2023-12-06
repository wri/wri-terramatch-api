<?php

namespace App\Http\Resources\V2\Applications;

use App\Http\Resources\V2\Forms\FormSubmissionLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationLiteResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'current_submission' => new FormSubmissionLiteResource($this->currentSubmission),

            'funding_programme_name' => data_get($this->fundingProgramme, 'name'),
            'funding_programme_uuid' => data_get($this->fundingProgramme, 'uuid'),
            'funding_programme_status' => data_get($this->fundingProgramme, 'status'),
            'organisation_name' => data_get($this->organisation, 'name'),
            'organisation_uuid' => data_get($this->organisation, 'uuid'),
            'project_pitch_uuid' => $this->project_pitch_uuid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $data;
    }
}
