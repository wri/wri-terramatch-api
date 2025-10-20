<?php

namespace App\Http\Resources\V2\Forms;

use App\Http\Resources\V2\AuditResource;
use App\Http\Resources\V2\Stages\StageLiteResource;
use App\Models\V2\I18n\I18nTranslation;
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
        $translatedFeedbackFields = collect();

        collect($this->feedback_fields)->each(function ($field) use ($translatedFeedbackFields) {
            $label = $field;

            $i18nTranslations = I18nTranslation::where('long_value', $label)->get('i18n_item_id');
            if ($i18nTranslations->isEmpty()) {
                $i18nTranslations = I18nTranslation::where('short_value', $label)->get('i18n_item_id');
            }

            if ($i18nTranslations->isEmpty()) {
                $translatedFeedbackFields->push($label);

                return;
            }

            $currentLanguage = App::getLocale() === 'en-US' ? 'en' : App::getLocale();
            $i18nItemIds = $i18nTranslations->pluck('i18n_item_id')->unique();

            $currentLanguageTranslation = I18nTranslation::whereIn('i18n_item_id', $i18nItemIds)
                ->where('language', $currentLanguage)
                ->get();

            if ($currentLanguageTranslation->isEmpty() && $currentLanguage !== 'en') {
                $currentLanguageTranslation = I18nTranslation::whereIn('i18n_item_id', $i18nItemIds)
                    ->where('language', 'en')
                    ->get();
            }

            if ($currentLanguageTranslation->isEmpty()) {
                $translatedFeedbackFields->push($label);

                return;
            }

            $currentLanguageTranslation->each(function ($translation) use ($translatedFeedbackFields) {
                $translatedFeedbackFields->push($translation->long_value ?? $translation->short_value);
            });
        });

        $translatedFeedbackFields = $translatedFeedbackFields->unique()->values()->all();

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'form_uuid' => $this->form_id,
            'framework_key' => $this->form->framework_key,
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
