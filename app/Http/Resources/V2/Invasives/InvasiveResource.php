<?php

namespace App\Http\Resources\V2\Invasives;

use Illuminate\Http\Resources\Json\JsonResource;

class InvasiveResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'name' => $this->name,
        ];
    }
}
