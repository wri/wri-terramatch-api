<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class FormQuestionResource extends JsonResource
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
        $data = Arr::whereNotNull([
            'uuid' => $this->uuid,
            'input_type' => $this->input_type,
            'name' => $this->name,
            'label' => $this->translated_label,
            'placeholder' => $this->translated_placeholder,
            'description' => $this->translated_description,
            'validation' => is_string($this->validation) ? json_decode($this->validation) : $this->validation,
            'multichoice' => $this->multichoice ? true : false,
            'collection' => $this->collection,
            'order' => $this->order,
            'options_list' => $this->options_list,
            'options_other' => $this->options_other,
            'additional_text' => $this->additional_text,
            'additional_url' => $this->additional_url,
            'parent_id' => $this->parent_id,
            'show_on_parent_condition' => $this->show_on_parent_condition,
            'linked_field_key' => $this->linked_field_key,
            'reference' => $this->buildReference($this->params),
        ]);

        if (count($this->children) > 0) {
            $data['children'] = (new FormQuestionCollection($this->children))->params($this->params);
        }

        if (count($this->options) > 0) {
            $data['options'] = FormQuestionOptionResource::collection($this->options);
        }

        if (count($this->tableHeaders) > 0) {
            $data['table_headers'] = FormTableHeaderResource::collection($this->tableHeaders);
        }

        if (! empty($this->additional_props)) {
            foreach ($this->additional_props as $key => $value) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    private function buildReference(array $params = null): ?array
    {
        if (empty($this->linked_field_key)) {
            return null;
        }
        $linkedInfo = $this->getLinkedFieldInfo($params);

        switch ($this->input_type) {
            case 'file':
            case 'image':
                return [
                    'model' => data_get($linkedInfo, 'model-key'),
                    'uuid' => data_get($linkedInfo, 'uuid'),
                    'collection' => data_get($linkedInfo, 'property'),
                ];
            case 'treeSpecies':
            case 'conditional':
            case 'dataTable':
            case 'fundingTypes':
            case 'tableInput':
            case 'selectImage':
            case 'coreTeamLeader':
            case 'leadershipTeam':
            case 'workdays':
                return [
                    'model' => data_get($linkedInfo, 'model-key'),
                    'uuid' => data_get($linkedInfo, 'uuid'),
                ];
            default:
                return null;
        }
    }
}
