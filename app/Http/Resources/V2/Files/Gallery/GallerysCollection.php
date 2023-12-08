<?php

namespace App\Http\Resources\V2\Files\Gallery;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GallerysCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => GalleryResource::collection($this->collection)];
    }
}
