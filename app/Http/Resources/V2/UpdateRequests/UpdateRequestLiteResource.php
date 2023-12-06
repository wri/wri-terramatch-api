<?php

namespace App\Http\Resources\V2\UpdateRequests;

use Illuminate\Http\Resources\Json\JsonResource;

class UpdateRequestLiteResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'framework_key' => $this->framework_key,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'comments' => $this->comments,
        ];

        return $data;
    }
}
