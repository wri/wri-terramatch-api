<?php

namespace App\Exports\V2;

use App\Helpers\LinkedFieldsHelper;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Organisation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

abstract class BaseExportFormSubmission implements WithHeadings, WithMapping
{
    use Exportable;

    protected $fieldMap = [];

    protected $auditFields = ['deleted_at' => 'Deleted At', 'created_at' => 'Created_at', 'updated_at' => 'Updated At'];

    protected $initialHeadings = [];

    protected function getAnswer(array $field, array $answers, string $frameworkKey): string
    {
        $answer = data_get($answers, $field['uuid']);

        $readableOptionsFields = ['select', 'radio',  'checkbox', 'imageSelect', 'fundingType', 'leadershipTeam', 'coreTeamLeaders', 'ownershipStake'];
        if (in_array(data_get($field, 'input_type'), $readableOptionsFields)) {
            $question = FormQuestion::isUuid($field['uuid'])->first();
            $answer = $this->getReadableOptionsValue($question, $answer);
        }

        if (data_get($field, 'input_type') == 'dataTable' || data_get($field, 'input_type' == 'tableInput')) {
            /**
             * Data tables have form questions nested in them
             * So we re-call the same method for each of the
             * form questions
             */
            $nestedQuestions = data_get($field['additional_props'], 'questions');
            foreach ($nestedQuestions as $question) {
                $this->getAnswer($question, $answers, $frameworkKey);
            }
        }

        if (is_array($answer)) {
            $list = [];
            foreach ($answer as $item) {
                $list[] = data_get($field, 'input_type') . '??' . $item;
            }

            return implode('|', $list);
        }

        if (is_object($answer)) {
            switch (data_get($field, 'input_type')) {
                case 'date':
                    return $answer->format('Y-m-d H:i:s');
                case 'file':
                    $list = [];
                    foreach ($answer as $fileProps) {
                        $list[] = is_object($fileProps) ? $fileProps->getUrl() : json_encode($fileProps);
                    }

                    return implode(' | ', $list);
                case 'treeSpecies':
                case 'seedings':
                    return $this->stringifyModel($answer, ['name', 'amount']);

                case 'workdays':
                case 'restorationPartners':
                    $list = [];
                    $demographical = $answer->first();
                    if ($demographical == null) {
                        return '';
                    }

                    $types = ['gender' => [], 'age' => [], 'ethnicity' => [], 'caste' => []];
                    foreach ($demographical->demographics as $demographic) {
                        $value = match ($demographic->type) {
                            'ethnicity' => [$demographic->amount, $demographic->subtype, $demographic->name],
                            default => [$demographic->amount, $demographic->name],
                        };
                        $types[$demographic['type']][] = implode(':', $value);
                    }
                    $list[] = 'gender:(' . implode(')(', $types['gender']) . ')';
                    $list[] = 'age:(' . implode(')(', $types['age']) . ')';
                    if ($frameworkKey == 'hbf') {
                        $list[] = 'caste:(' . implode(')(', $types['caste']) . ')';
                    } else {
                        $list[] = 'ethnicity:(' . implode(')(', $types['ethnicity']) . ')';
                    }

                    return implode('|', $list);

                case 'leadershipTeam':
                    return $this->stringifyModel($answer, ['first_name', 'last_name', 'position', 'gender', 'age',]);

                case 'coreTeamLeaders':
                    return $this->stringifyModel($answer, ['first_name', 'last_name', 'position', 'gender', 'age', 'role']);

                case 'fundingType':
                    return $this->stringifyModel($answer, ['type', 'source', 'amount', 'year']);

                case 'ownershipStake':
                    return $this->stringifyModel($answer, ['first_name', 'last_name', 'title', 'gender', 'percent_ownership', 'year_of_birth',]);

                default:
                    return json_encode($answer);
            }
        }

        return $answer ?? '';
    }

    protected function generateFieldMap(Form $form): array
    {
        $mapping = collect([]);

        foreach ($form->sections as $section) {
            foreach ($section->questions as $question) {
                $this->addQuestionToMapping($question, $mapping, $form);
            }
        }

        return $mapping->toArray();
    }

    protected function addQuestionToMapping(FormQuestion $question, Collection $mapping, Form $form)
    {
        if (
            ($mapping->where('uuid', $question->uuid)->count() === 0) and
            (! is_null($question->linked_field_key)) and
            (! in_array($question->input_type, ['tableInput']))
        ) {
            $mapping->add([
                'uuid' => $question->uuid,
                'form_uuid' => $form->uuid,
                'linked_field_key' => $question->linked_field_key,
                'input_type' => $question->input_type,
                'label' => $question->label,
                'heading' => LinkedFieldsHelper::getPropertyNameByFieldKey($question->linked_field_key),
            ]);
        }

        foreach ($question->children as $childQuestion) {
            $this->addQuestionToMapping($childQuestion, $mapping, $form);
        }
    }

    protected function handleOrganisationFiles(Organisation $organisation, string $collection): string
    {
        $list = [];
        foreach ($organisation->getMedia($collection) as $fileProps) {
            $list[] = $fileProps->getUrl();
        }

        return implode(' | ', $list);
    }

    protected function getReadableOptionsValue(FormQuestion $question, $answer)
    {
        $options = $this->getAvailableOptions($question);

        if (! $options) {
            return $answer;
        }

        if (is_array($answer)) {
            $list = [];
            foreach ($answer as $item) {
                $list[] = $options[$item] ?? $item;
            }

            return $list;
        }

        return $options[$answer] ?? $answer;
    }

    protected function getAvailableOptions($question): ?array
    {
        if (! empty($question->options_list)) {
            switch ($question->options_list) {
                case 'countries':
                    return config('wri.countries');
                case 'months':
                    return config('wri.months');
                default:
                    return null;
            }
        }

        return $question->options()->pluck('label', 'slug', )->toArray();
    }

    protected function stringifyModel(AnonymousResourceCollection $answer, array $propertyList)
    {
        $list = [];
        foreach ($answer as $item) {
            $data = [];
            foreach ($propertyList as $prop) {
                $data[] = data_get($item, $prop, '');
            }
            $list[] = implode(':', $data);
        }

        return implode('|', $list);
    }
}
