<?php

namespace App\Http\Resources\V2\Stratas;

use Illuminate\Http\Resources\Json\JsonResource;

class StrataResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'description' => $this->description,
            'extent' => $this->extent,
        ];
    }
}
