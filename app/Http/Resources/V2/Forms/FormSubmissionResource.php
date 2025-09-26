<?php

namespace App\Http\Resources\V2\Forms;

use App\Http\Resources\V2\AuditResource;
use App\Http\Resources\V2\Stages\StageLiteResource;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class FormSubmissionResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $translatedFeedbackFields = collect($this->feedback_fields)->map(function ($field) {
            $label = $field;
            $type = 'short';
            if (strlen($label) > 255) {
                $type = 'long';
            }
            $i18nItemQuery = I18nItem::where('type', $type);
            if ($type === 'long') {
                $i18nItemQuery->where('long_value', $label);
            } else {
                $i18nItemQuery->where('short_value', $label);
            }
            $i18nItem = $i18nItemQuery->first();
            if (! $i18nItem) {
                return $label;
            }
            $translatedValue = $i18nItem->getTranslated(App::getLocale());
            if (! $translatedValue) {
                return $label;
            }
            if ($type === 'long') {
                return $translatedValue->long_value;
            } else {
                return $translatedValue->short_value;
            }
        });

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'form_uuid' => $this->form_id,
            'answers' => $this->getAllAnswers(['organisation' => $this->organisation, 'project-pitch' => $this->projectPitch]),
            'status' => $this->status,
            'application_uuid' => data_get($this->application, 'uuid'),
            'organisation_uuid' => $this->organisation_uuid,
            'organisation_attributes' => [
                'uuid' => $this->organisation_uuid,
                'type' => $this->organisation?->type,
                'currency' => $this->organisation?->currency,
                'start_month' => $this->organisation?->fin_start_month,
            ],
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'translated_feedback_fields' => $translatedFeedbackFields,
            'stage' => new StageLiteResource($this->stage),
            'next_stage_uuid' => $this->getNextStageUuid(),
            'previous_stage_uuid' => $this->getPreviousStageUuid(),
            'audits' => AuditResource::collection($this->audits),
            'updated_by' => $this->user_id,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'project_pitch_uuid' => $this->project_pitch_uuid,
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
