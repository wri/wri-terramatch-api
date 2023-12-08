<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\JsonResource;

class FormOptionListOptionResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'form_option_list_id' => $this->form_option_list_id,
            'slug' => $this->slug,
            'alt_value' => $this->alt_value,
            'label' => $this->label,
            'image_url' => ! empty($this->image_url) ? url($this->image_url) : null,
        ];
    }
}
