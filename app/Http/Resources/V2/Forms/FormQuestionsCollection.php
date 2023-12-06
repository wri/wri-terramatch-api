<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FormQuestionsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => FormQuestionResource::collection($this->collection)];
    }
}
