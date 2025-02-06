<?php

namespace App\Models\Traits;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\V2\Forms\Form;

trait UsesLinkedFields
{
    private ?Form $frameworkModelForm = null;

    public function getForm(): Form
    {
        if (! is_null($this->form)) {
            // Some classes that use this trait have a direct database link to the form.
            return $this->form;
        }

        if (is_null($this->frameworkModelForm)) {
            $this->frameworkModelForm = Form::where('model', get_class($this))
                ->where('framework_key', $this->framework_key)
                ->first();
        }

        return $this->frameworkModelForm;
    }

    public function getFormConfig(): ?array
    {
        return config('wri.linked-fields.models.' . $this->shortName);
    }

    public function updateAllAnswers(array $input): array
    {
        $localAnswers = [];
        foreach ($this->getform()->sections as $section) {
            foreach ($section->questions as $question) {
                if ($question->input_type !== 'conditional' && ! empty($question->linked_field_key)) {
                    $linkedFieldInfo = $question->getLinkedFieldInfo([
                        'organisation' => $this->organisation,
                        'project-pitch' => $this->projectPitch,
                    ]);
                    if (! empty($linkedFieldInfo)) {
                        $hidden = ! empty($question->parent_id) && $question->show_on_parent_condition &&
                            data_get($input, $question->parent_id) === false;
                        $this->updateLinkedFieldValue($linkedFieldInfo, data_get($input, $question->uuid), $hidden);
                    }
                }
                $localAnswers[$question->uuid] = data_get($input, $question->uuid);
            }
        }

        return $localAnswers;
    }

    public function updateFromForm(array $formData): void
    {
        $form = $this->getForm();
        $formConfig = $this->getFormConfig();
        $fieldsConfig = data_get($formConfig, 'fields', []);
        $relationsConfig = data_get($formConfig, 'relations', []);
        $localAnswers = [];
        $entityProps = [];

        foreach ($form->sections as $section) {
            foreach ($section->questions as $question) {
                if ($question->input_type !== 'conditional') {
                    $fieldConfig = data_get($fieldsConfig, $question->linked_field_key);
                    if ($fieldConfig != null) {
                        $property = data_get($fieldConfig, 'property', null);
                        $value = data_get($formData, $question->uuid, null);

                        if (! is_null($value)) {
                            if (empty($property)) {
                                $localAnswers[$question->uuid] = data_get($formData, $question->uuid);
                            }

                            $entityProps[$property] = $value;
                        }
                    } else {
                        $property = data_get($relationsConfig, "$question->linked_field_key.property");
                        if (! empty($property)) {
                            $inputType = data_get($relationsConfig, "$question->linked_field_key.input_type");
                            $hidden = ! empty($question->parent_id) && $question->show_on_parent_condition &&
                                $formData[$question->parent_id] === false;
                            $this->syncRelation($property, $inputType, collect(data_get($formData, $question->uuid)), $hidden);
                        }
                    }

                } else {
                    $localAnswers[$question->uuid] = data_get($formData, $question->uuid, null);
                    $entityProps['answers'] = $localAnswers;
                }
            }
        }

        $this->update($entityProps);
    }

    public function calculateCompletion(Form $form): int
    {
        $questionCount = 0;
        $answeredCount = 0;
        $answers = [];

        foreach ($form->sections as $section) {
            foreach ($section->questions as $question) {
                $required = data_get($question, 'validation.required', true);
                $conditionalOn = data_get($question, 'show_on_parent_condition')
                    ? data_get($question, 'parent_id')
                    : null;
                $value = null;

                $linkedFieldInfo = $question->getLinkedFieldInfo(['model_uuid' => $this->uuid]);
                if (! empty($linkedFieldInfo)) {
                    $value = $this->getEntityLinkedFieldValue($linkedFieldInfo);
                } else {
                    $value = data_get($this->answers, $question->uuid);
                }

                $answers[$question->uuid] = [
                    'required' => $required,
                    'conditionalOn' => $conditionalOn,
                    'value' => $value,
                ];
            }
        }

        foreach ($answers as $answer) {
            if (! $answer['required']) {
                // don't count it if the question wasn't required
                continue;
            }

            if (! empty($answers['conditionalOn'])) {
                $conditional = $answers['conditional'];
                if (empty($conditional) || ! $conditional['value']) {
                    // don't count it if the question wasn't shown to the user because the parent conditional is false
                    // or missing
                    continue;
                }
            }

            $questionCount++;
            if (! is_null($answer['value'])) {
                $answeredCount++;
            }
        }

        return $questionCount == 0 ? 100 : round(($answeredCount / $questionCount) * 100);
    }

    public function getAllAnswers(array $params = []): array
    {
        $answers = [];

        foreach ($this->getForm()->sections as $section) {
            foreach ($section->questions as $question) {
                if ($question->input_type !== 'conditional' && ! empty($question->linked_field_key)) {
                    $linkedFieldInfo = $question->getLinkedFieldInfo($params);

                    if (! empty($linkedFieldInfo)) {
                        $answers[$question->uuid] = $this->getLinkedFieldValue($linkedFieldInfo);
                    }
                } else {
                    $answers[$question->uuid] = data_get($this->answers, $question->uuid);
                }
            }
        }

        return $answers;
    }

    public function getEntityAnswers(Form $form): array
    {
        $answers = [];
        $modelAnswers = $this->answers;

        foreach ($form->sections as $section) {
            foreach ($section->questions as $question) {
                $linkedFieldInfo = $question->getLinkedFieldInfo(['model_uuid' => $this->uuid]);
                if (! empty($linkedFieldInfo)) {
                    $answers[$question->uuid] = $this->getEntityLinkedFieldValue($linkedFieldInfo);
                } else {
                    $answers[$question->uuid] = data_get($modelAnswers, $question->uuid);
                }
            }
        }

        return $answers;
    }

    private function getEntityLinkedFieldValue(array $linkedFieldInfo)
    {
        $property = $linkedFieldInfo['property'];

        return $this->mapValue($this, $property, $linkedFieldInfo);
    }

    private function getLinkedFieldValue(array $linkedFieldInfo)
    {
        $class = app($linkedFieldInfo['model']);
        $model = $class::isUuid($linkedFieldInfo['uuid'])->first();
        $property = $linkedFieldInfo['property'];

        if (empty($property) || empty($model)) {
            return null;
        }

        return $this->mapValue($model, $property, $linkedFieldInfo);
    }

    private function mapValue($model, string $property, array $linkedFieldInfo)
    {
        switch ($linkedFieldInfo['link-type']) {
            case 'fields':
                return data_get($model, $property);
            case 'file-collections' :
                $colCfg = data_get($model->fileConfiguration, $property);

                return $model->getFileResource($property, $colCfg);
            case 'relations' :
                $relation = data_get($linkedFieldInfo, 'property');
                $resource = data_get($linkedFieldInfo, 'resource');
                if (empty($resource)) {
                    return $model->$relation;
                }
                if (empty($model->$relation)) {
                    return [];
                }

                return $resource::collection($model->$relation);
            default:
                return null;
        }
    }

    private function updateLinkedFieldValue(array $linkedFieldInfo, $answer, bool $hidden): void
    {
        $class = app($linkedFieldInfo['model']);
        $model = $class::isUuid($linkedFieldInfo['uuid'])->first();
        $property = $linkedFieldInfo['property'];

        if (empty($model) || empty($property)) {
            return ;
        }

        if ($linkedFieldInfo['link-type'] == 'fields') {
            $model->$property = $answer;
            $model->save();
        } elseif ($linkedFieldInfo['link-type'] == 'relations') {
            $inputType = data_get($linkedFieldInfo, 'input_type');
            $this->syncRelation($property, $inputType, collect($answer), $hidden, $model);
        }
    }

    private function syncRelation(string $property, string $inputType, $data, bool $hidden, $entity = null): void
    {
        $entity ??= $this;

        if (! in_array($inputType, [
            'treeSpecies',
            'disturbances',
            'workdays',
            'restorationPartners',
            'stratas',
            'invasive',
            'seedings',
        ])) {
            return;
        }

        $class = get_class($entity->$property()->make());
        if (is_a($class, HandlesLinkedFieldSync::class, true)) {
            $class::syncRelation($entity, $property, $inputType, $data, $hidden);

            return;
        }

        $entity->$property()->whereNotIn('uuid', $data->pluck('uuid')->filter())->delete();

        // This would be better as a bulk operation, but too much processing is required to make that feasible
        // in Eloquent (upsert isn't supported on MorphMany, for instance), and these sets will always be small
        // so doing them one at a time is OK.
        $entries = $entity->$property()->get();
        foreach ($data as $entry) {
            $entry['hidden'] = $hidden;

            $model = null;
            if (! empty($entry['uuid'])) {
                $model = $entries->firstWhere('uuid', $entry['uuid']);
            }
            if ($model != null) {
                $model->update($entry);
            } else {
                // protection against clashing with a deleted entry
                if (! empty($entry['uuid']) && $entity->$property()->onlyTrashed()->where('uuid', $entry['uuid'])->exists()) {
                    unset($entry['uuid']);
                }
                $entity->$property()->create($entry);
            }
        }
    }
}
