<?php

namespace App\Http\Resources\V2\Files;

;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FilesCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => FileResource::collection($this->collection)];
    }
}
