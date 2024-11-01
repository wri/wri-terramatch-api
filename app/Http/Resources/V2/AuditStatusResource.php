<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class AuditStatusResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $this->status,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'comment' => $this->comment,
            'type' => $this->type,
            'request_removed' => $this->request_removed,
            'date_created' => $this->date_created,
            'attachments' => $this->attachments,
        ];
    }
}
