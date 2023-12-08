<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\JsonResource;

class FormQuestionOptionResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'form_question_id' => $this->form_question_id,
            'label' => $this->translated_label,
            'order' => $this->order,
            'image_url' => ! empty($this->image_url) ? url($this->image_url) : null,
        ];

        return $this->appendFilesToResource($data);
    }
}
