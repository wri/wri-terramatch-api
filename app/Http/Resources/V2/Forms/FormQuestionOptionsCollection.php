<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FormQuestionOptionsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => FormQuestionOptionResource::collection($this->collection)];
    }
}
