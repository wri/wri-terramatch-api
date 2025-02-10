<?php

namespace App\Http\Resources\V2\ImpactStory;

use Illuminate\Http\Resources\Json\JsonResource;

class ImpactStoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'organization_id' => $this->organization_id,
            'date' => $this->date,
            'category' => $this->category,
            'thumbnail' => $this->thumbnail,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
