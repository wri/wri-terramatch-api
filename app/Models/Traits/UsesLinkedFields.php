<?php

namespace App\Models\Traits;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\ProjectPolygon;
use App\StateMachines\EntityStatusStateMachine;

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

    public function updateAllAnswers(array $input, bool $isApproval = false): array
    {
        $localAnswers = [];
        foreach ($this->getform()->sections as $section) {
            /** @var FormQuestion $question */
            foreach ($section->questions as $question) {
                if ($question->input_type !== 'conditional' && ! empty($question->linked_field_key)) {
                    $linkedFieldInfo = $question->getLinkedFieldInfo([
                        'organisation' => $this->organisation,
                        'project-pitch' => $this->projectPitch,
                    ]);
                    if (! empty($linkedFieldInfo)) {
                        $hidden = ! empty($question->parent_id) && $question->show_on_parent_condition &&
                            data_get($input, $question->parent_id) === false;
                        $this->updateLinkedFieldValue($linkedFieldInfo, data_get($input, $question->uuid), $hidden, $isApproval);
                    }
                }
                $localAnswers[$question->uuid] = data_get($input, $question->uuid);
            }
        }

        return $localAnswers;
    }

    public function updateFromForm(array $formData, bool $isApproval = false): void
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

                        $validation = data_get($question, 'validation', null);
                        $isDate = $question->input_type === 'date' && empty($validation['required']);
                        if (! is_null($value) || $isDate) {
                            if (empty($property)) {
                                $localAnswers[$question->uuid] = data_get($formData, $question->uuid);
                            }

                            if ($question->linked_field_key == 'pro-rep-landscape-com-con' && ! empty($question->parent_id) &&
                                data_get($formData, $question->parent_id) === true) {
                                $entityProps[$property] = '';
                            } else {
                                $entityProps[$property] = $value;
                            }
                        }
                    } else {
                        $property = data_get($relationsConfig, "$question->linked_field_key.property");
                        if (! empty($property)) {
                            $inputType = data_get($relationsConfig, "$question->linked_field_key.input_type");
                            $hidden = ! empty($question->parent_id) && $question->show_on_parent_condition &&
                                data_get($formData, $question->parent_id) === false;
                            $this->syncRelation($property, $inputType, collect(data_get($formData, $question->uuid)), $hidden, null, $isApproval);
                        }
                    }

                } else {
                    $localAnswers[$question->uuid] = data_get($formData, $question->uuid, null);
                    $entityProps['answers'] = $localAnswers;
                }
            }
        }

        $this->update($entityProps);

        if ($this->status == EntityStatusStateMachine::APPROVED) {
            // This state only occurs when an admin is editing an already approved entity. Under these circumstances,
            // we'd rather see the entity saved with the clean data view than preserve any temporarily set data the
            // admin might have put in place.
            $this->cleanConditionalAnswers($form);
        }
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
                $inputType = strtolower((string) data_get($linkedFieldInfo, 'input_type'));
                if ($inputType === 'mapinput' && $property === 'proj_boundary') {
                    $geojson = $this->getProjectBoundaryGeometryFromPolygons($model);
                    if ($geojson != null) {
                        return $geojson;
                    }
                }

                return data_get($model, $property);
            case 'file-collections':
                $colCfg = data_get($model->fileConfiguration, $property);

                return $model->getFileResource($property, $colCfg);
            case 'relations':
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

    private function getProjectBoundaryGeometryFromPolygons($entity): ?array
    {
        $projectPolygon = ProjectPolygon::where('entity_type', get_class($entity))
            ->where('entity_id', $entity->id)
            ->orderByDesc('created_at')
            ->first();

        if ($projectPolygon == null) {
            return null;
        }

        $row = PolygonGeometry::isUuid($projectPolygon->poly_uuid)
            ->selectRaw('ST_AsGeoJSON(geom) as geojson_string')
            ->first();

        if ($row == null) {
            return null;
        }
        if (empty($row->geojson_string)) {
            return null;
        }

        $decoded = json_decode($row->geojson_string, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function updateLinkedFieldValue(array $linkedFieldInfo, $answer, bool $hidden, bool $isApproval = false): void
    {
        $class = app($linkedFieldInfo['model']);
        $model = $class::isUuid($linkedFieldInfo['uuid'])->first();
        $property = $linkedFieldInfo['property'];

        if (empty($model) || empty($property)) {
            return;
        }

        if ($linkedFieldInfo['link-type'] == 'fields') {
            $model->$property = $answer;
            $model->save();
        } elseif ($linkedFieldInfo['link-type'] == 'relations') {
            $inputType = data_get($linkedFieldInfo, 'input_type');
            $this->syncRelation($property, $inputType, collect($answer), $hidden, $model, $isApproval);
        }
    }

    private function syncRelation(string $property, string $inputType, $data, bool $hidden, $entity = null, bool $isApproval = false): void
    {
        $entity ??= $this;

        if (
            ! in_array($inputType, [
                'treeSpecies',
                'disturbances',
                'workdays',
                'restorationPartners',
                'jobs',
                'employees',
                'volunteers',
                'allBeneficiaries',
                'trainingBeneficiaries',
                'indirectBeneficiaries',
                'associates',
                'stratas',
                'invasive',
                'seedings',
                'financialIndicators',
            ])
        ) {
            return;
        }

        $class = get_class($entity->$property()->make());
        if (is_a($class, HandlesLinkedFieldSync::class, true)) {
            $class::syncRelation($entity, $property, $inputType, $data, $hidden, $isApproval);

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

    public function cleanConditionalAnswers(Form $form): void
    {
        $formConfig = $this->getFormConfig();
        $fieldsConfig = data_get($formConfig, 'fields', []);
        $modelAnswers = $this->answers;
        $entityProps = [];

        $childQuestions = [];
        $conditionalValues = [];

        foreach ($form->sections as $section) {
            foreach ($section->questions as $question) {
                if ($question->input_type !== 'conditional') {
                    if (! is_null($question->parent_id)) {
                        $childQuestions[] = $question;
                    }
                } else {
                    $value = data_get($modelAnswers, $question->uuid, null);
                    if (! is_null($value)) {
                        $conditionalValues[$question->uuid] = $value;
                    }
                }
            }
        }

        foreach ($childQuestions as $child) {
            if (array_key_exists($child->parent_id, $conditionalValues) && $child->show_on_parent_condition != $conditionalValues[$child->parent_id]) {
                $fieldConfig = data_get($fieldsConfig, $child->linked_field_key);
                $property = data_get($fieldConfig, 'property');
                if ($this->isPlainField($child->input_type) && ! empty($property)) {
                    $entityProps[$property] = null;
                }
            }
        }

        $this->update($entityProps);
    }

    private function isPlainField($input_type)
    {
        $plainFields = ['long-text', 'date', 'number', 'text', 'number-percentage', 'boolean'];

        return in_array($input_type, $plainFields);
    }
}
