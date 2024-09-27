<?php

namespace App\Http\Resources\V2\Files;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'url' => $this->getFullUrl(),
            'thumb_url' => $this->getFullUrl('thumbnail'),
            'collection_name' => $this->collection_name,
            'title' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'lat' => (float) $this->lat ?? null,
            'lng' => (float) $this->lng ?? null,
            'is_public' => (bool) $this->is_public,
            'is_cover' => (bool) $this->is_cover,
            'created_at' => $this->created_at,
            'description' => $this->description,
            'photographer' => $this->photographer
        ];
    }
}
