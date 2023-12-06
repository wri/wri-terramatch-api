<?php

namespace App\Http\Resources\V2\Files\Gallery;

use Illuminate\Http\Resources\Json\JsonResource;

class GalleryLiteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'thumb_url' => $this->getFullUrl('thumbnail'),
            'file_url' => $this->getFullUrl(),
            'location' => [
                'lat' => (float) $this->lat ?? null,
                'lng' => (float) $this->lng ?? null,
            ],
        ];
    }
}
