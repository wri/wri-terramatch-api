<?php

namespace App\Http\Resources\V2\General;

use Illuminate\Http\Resources\Json\JsonResource;

class KeyValueResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => data_get($this, 'uuid'),
            'name' => data_get($this, 'name'),
            'input_type' => data_get($this, 'input_type'),
            'options' => data_get($this, 'options'),
            'option_list_key' => data_get($this, 'option_list_key'),
            'multichoice' => data_get($this, 'multichoice'),
        ];
    }
}
