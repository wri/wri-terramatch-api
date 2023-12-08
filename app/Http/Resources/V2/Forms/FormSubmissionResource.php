<?php

namespace App\Http\Resources\V2\Forms;

use App\Http\Resources\V2\AuditResource;
use App\Http\Resources\V2\Stages\StageLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FormSubmissionResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $params = [
            'organisation_uuid' => $this->organisation_uuid,
            'project_pitch_uuid' => $this->project_pitch_uuid,
        ];

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'form' => (new FormResource($this->form))
                ->params($params),
            'answers' => $this->getAllAnswers(['organisation' => $this->organisation, 'project-pitch' => $this->projectPitch]),
            'status' => $this->status,
            'application_uuid' => data_get($this->application, 'uuid'),
            'organisation_uuid' => $this->organisation_uuid,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'stage' => new StageLiteResource($this->stage),
            'next_stage_uuid' => $this->getNextStageUuid(),
            'previous_stage_uuid' => $this->getPreviousStageUuid(),
            'audits' => AuditResource::collection($this->audits),
            'updated_by' => $this->user_id,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getNextStageUuid()
    {
        $stage = $this->stage;
        if (! $stage) {
            return null;
        }

        return data_get($stage->nextStage, 'uuid', null);
    }

    private function getPreviousStageUuid()
    {
        $stage = $this->stage;
        if (! $stage) {
            return null;
        }

        return data_get($stage->previousStage, 'uuid', null);
    }
}
