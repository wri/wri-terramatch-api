<?php

namespace App\Http\Resources\V2\Applications;

use App\Http\Resources\V2\Forms\FormSubmissionResource;
use App\Http\Resources\V2\FundingProgrammes\FundingProgrammeResource;
use App\Http\Resources\V2\Organisation\OrganisationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'form_submissions' => FormSubmissionResource::collection($this->formSubmissions),
            'current_submission' => new FormSubmissionResource($this->currentSubmission),
            'funding_programme' => new FundingProgrammeResource($this->fundingProgramme),
            'organisation' => new OrganisationResource($this->organisation),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $data;
    }
}
