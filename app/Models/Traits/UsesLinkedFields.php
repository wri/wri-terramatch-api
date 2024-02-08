<?php

namespace App\Models\Traits;

use App\Models\V2\Forms\Form;

trait UsesLinkedFields
{
    public function updateAllAnswers(array $input): array
    {
        $localAnswers = [];
        foreach ($this->form->sections as $section) {
            foreach ($section->questions as $question) {
                if ($question->input_type !== 'conditional' && ! empty($question->linked_field_key)) {
                    $linkedFieldInfo = $question->getLinkedFieldInfo([
                        'organisation' => $this->organisation,
                        'project-pitch' => $this->projectPitch,
                    ]);
                    if (! empty($linkedFieldInfo)) {
                        $this->updateLinkedFieldValue($linkedFieldInfo, data_get($input, $question->uuid));
                    }
                }
                $localAnswers[$question->uuid] = data_get($input, $question->uuid);
            }
        }

        return $localAnswers;
    }

    public function mapEntityAnswers(array $input, Form $form, array $cfg): array
    {
        $localAnswers = [];
        $entityProps = [];
        foreach ($form->sections as $section) {
            foreach ($section->questions as $question) {
                if ($question->input_type !== 'conditional') {
                    $fieldConfig = data_get($cfg, $question->linked_field_key);
                    $property = data_get($fieldConfig, 'property', null);
                    $value = data_get($input, $question->uuid, null);

                    if (! is_null($value)) {
                        if (empty($property)) {
                            $localAnswers[$question->uuid] = data_get($input, $question->uuid);
                        }

                        $entityProps[$property] = $value;
                    }
                } else {
                    $localAnswers[$question->uuid] = data_get($input, $question->uuid, null);
                    $entityProps['answers'] = $localAnswers;
                }
            }
        }

        return $entityProps;
    }

    public function calculateCompletion(Form $form): int
    {
        $questionCount = 0;
        $answeredCount = 0;

        foreach ($form->sections as $section) {
            foreach ($section->questions as $question) {
                $linkedFieldInfo = $question->getLinkedFieldInfo(['model_uuid' => $this->uuid]);
                if (! empty($linkedFieldInfo)) {
                    $answers[$question->uuid] = $this->getEntityLinkedFieldValue($linkedFieldInfo);
                }
            }
        }

        foreach ($answers as $answer) {
            $questionCount++;
            if (! empty($answer)) {
                $answeredCount++;
            }
        }

        return round(($answeredCount / $questionCount) * 100);
    }

    public function getAllAnswers(array $params = []): array
    {
        $answers = [];

        foreach ($this->form->sections as $section) {
            foreach ($section->questions as $question) {
                if ($question->input_type !== 'conditional' && ! empty($question->linked_field_key)) {
                    $linkedFieldInfo = $question->getLinkedFieldInfo($params);

                    if (! empty($linkedFieldInfo)) {
                        $answers[$question->uuid] = $this->getLinkedFieldValue($linkedFieldInfo);
                    }
                }
                $answers[$question->uuid] = data_get($this->answers, $question->uuid);
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

    private function updateLinkedFieldValue(array $linkedFieldInfo, $answer): void
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
        }
    }

    public function getCurrentForm(): Form
    {
        return Form::where('model', get_class($this))
            ->where('framework_key', $this->framework_key)
            ->first();
    }

    public function getFormConfig(): ?array
    {
        return config('wri.linked-fields.models.' . $this->shortName);
    }
}
