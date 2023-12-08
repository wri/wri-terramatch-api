<?php

namespace App\Http\Resources\V2\General;

use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => data_get($this, 'uuid'),
            'label' => data_get($this, 'label'),
            'name' => data_get($this, 'name'),
            'input_type' => data_get($this, 'input_type'),
            'options' => data_get($this, 'options'),
            'option_list_key' => data_get($this, 'option_list_key'),
            'multichoice' => data_get($this, 'multichoice'),
            'model_key' => data_get($this, 'model_key'),
            'collection' => data_get($this, 'collection'),
        ];
    }
}
