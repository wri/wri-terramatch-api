<?php

namespace App\Http\Resources\V2\Files\Gallery;

;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GallerysLiteCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => GalleryLiteResource::collection($this->collection)];
    }
}
