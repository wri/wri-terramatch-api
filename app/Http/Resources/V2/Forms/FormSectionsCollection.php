<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FormSectionsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => FormSectionResource::collection($this->collection)];
    }
}
