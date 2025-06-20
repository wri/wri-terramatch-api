<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\JsonResource;

class FormLightResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'type' => $this->type,
            'published' => $this->published,
        ];
        return $this->appendFilesToResource($data);
    }
}
