<?php

namespace App\Http\Resources\V2\FundingProgrammes;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Http\Resources\V2\Stages\StagesCollection;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FundingProgrammeResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $firstStage = $this->stages()->first();
        $deadline = empty($firstStage) ? null : data_get($firstStage, 'deadline_at');

        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->translated_name,
            'description' => $this->translated_description,
            'location' => $this->translated_location,
            'read_more_url' => $this->read_more_url,
            'framework_key' => $this->framework_key,
            'deadline_at' => $deadline ? Carbon::parse($deadline, 'EST')->toISOString() : null,
            'status' => $this->status,
            'organisation_types' => $this->organisation_types,
            'stages' => new StagesCollection($this->stages),
            'organisations' => OrganisationLiteResource::collection($this->organisations),
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $this->appendFilesToResource($data);
    }
}
