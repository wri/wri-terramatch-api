<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class FormSectionResource extends JsonResource
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
            'form_id' => $this->form_id,
            'order' => $this->order,
            'title' => $this->translated_title,
            'subtitle' => $this->translated_subtitle,
            'description' => $this->translated_description,
            'form_questions' => (new FormQuestionCollection($this->nonDependantQuestions))
                ->params($this->params),
        ]);

        return $data;
    }
}
