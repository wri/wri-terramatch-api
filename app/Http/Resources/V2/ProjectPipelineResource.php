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
                'name' => $this->Name,
                'description' => $this->Description,
            ],
            'date' => $this->updated_at ? $this->updated_at->format('j F Y') : "", 
            'id' => $this->id,
            'SubmittedBy' => $this->SubmittedBy,
            'Program' => $this->Program,
            'Cohort' => $this->Cohort,
            'PublishFor' => $this->PublishFor,
            'URL' => $this->URL,
            'created_at' => $this->created_at,
            'CreatedDate' => $this->created_at ? $this->created_at->format('j F Y') : "",
            'ModifiedDate' => $this->updated_at ? $this->updated_at->format('j F Y') : "",
        ];
    }
}
