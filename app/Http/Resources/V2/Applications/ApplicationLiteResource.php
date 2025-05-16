<?php

namespace App\Http\Resources\V2\Applications;

use App\Http\Resources\V2\Forms\FormSubmissionLiteResource;
use App\Models\V2\FundingProgramme;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationLiteResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'current_submission' => new FormSubmissionLiteResource($this->currentSubmission),
            'funding_programme_name' => FundingProgramme::where('uuid', $this->funding_programme_uuid)->select('name')->first()['name'],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $data;
    }
}
