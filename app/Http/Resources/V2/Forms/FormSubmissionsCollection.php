<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FormSubmissionsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => FormSubmissionResource::collection($this->collection)];
    }
}
