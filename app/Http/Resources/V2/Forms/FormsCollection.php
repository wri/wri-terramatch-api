<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FormsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => FormResource::collection($this->collection)];
    }
}
