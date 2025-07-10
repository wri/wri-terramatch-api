<?php

namespace App\Http\Resources\V2\Forms;

class FormsLightCollection extends FormsCollection
{
    public function toArray($request)
    {
        return ['data' => FormLightResource::collection($this->collection)];
    }
}
