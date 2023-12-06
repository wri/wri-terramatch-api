<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\JsonResource;

class FormTableHeaderResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'form_question_id' => $this->form_question_id,
            'label' => $this->translated_label,
            'order' => $this->order,
        ];
    }
}
