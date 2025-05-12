<?php

namespace App\Http\Resources\V2\Forms;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    protected $params;

    public function params(array $params = null)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'version' => $this->version,
            'title' => $this->translated_title,
            'subtitle' => $this->translated_subtitle,
            'description' => $this->translated_description,
            'framework_key' => $this->framework_key,
            'type' => $this->type,
            'duration' => $this->duration,
            'documentation' => $this->documentation,
            'documentation_label' => $this->documentation_label,
            'deadline_at' => $this->deadline_at ? Carbon::parse($this->deadline_at, 'EST')->toISOString() : null,
            'submission_message' => $this->translated_submission_message,
            'published' => $this->published,
            'stage_id' => $this->stage_id,
            'funding_programme_uuid' => $this->stage?->funding_programme_id,
            'form_sections' => (new FormSectionCollection($this->sections))
                ->params($this->params),
        ];

        return $this->appendFilesToResource($data);
    }
}
