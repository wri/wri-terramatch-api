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
            'entity' => $this->entity,
            'entity_uuid' => $this->entity_uuid,
            'status' => $this->status,
            'comment' => $this->comment,
            'attachment_url' => $this->attachment_url,
            'date_created' => $this->date_created,
            'created_by' => $this->created_by,
        ];
    }
}
