<?php

namespace App\Http\Resources\V2\General;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'message' => data_get($this, 'message', ''),
        ];

        if (data_get($this, 'status')) {
            $data['status'] = data_get($this, 'status');
        }

        return $data;
    }
}
