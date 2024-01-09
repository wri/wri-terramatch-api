<?php

namespace App\Http\Resources\V2\FundingProgrammes;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FundingProgrammeLiteResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $firstStage = $this->stages()->first();
        $formUuid = empty($firstStage) ? null : data_get($firstStage->form, 'uuid');
        $deadline = $firstStage ? ($firstStage->deadline_at ? Carbon::parse($firstStage->deadline_at, 'EST')->toISOString() : null) : null;

        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->translated_name,
            'description' => $this->translated_description,
            'location' => $this->location,
            'read_more_url' => $this->read_more_url,
            'framework_key' => $this->framework_key,
            'status' => $this->status,
            'organisation_types' => $this->organisation_types,
            'first_form_uuid' => $formUuid,
            'deadline_at' => $deadline,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $this->appendFilesToResource($data);
    }
}
