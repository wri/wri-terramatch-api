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
        $imageUrl = null;

        if (!empty($this->image_url)) {
            $imageUrl = url($this->image_url);
        } elseif (!empty($this->getFirstMediaUrl('image'))) {
            $imageUrl = url($this->getFirstMediaUrl('image'));
        }

        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'form_question_id' => $this->form_question_id,
            'label' => $this->translated_label,
            'order' => $this->order,
            'image_url' => $imageUrl,
        ];

        return $this->appendFilesToResource($data);
    }
}
