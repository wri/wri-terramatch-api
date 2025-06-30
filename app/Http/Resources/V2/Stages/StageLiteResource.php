<?php

namespace App\Http\Resources\V2\Stages;

use App\Http\Resources\V2\Forms\FormLightResource;
use Illuminate\Http\Resources\Json\JsonResource;

class StageLiteResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'form' => new FormLightResource($this->form)
        ];
    }
}
