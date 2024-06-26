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
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'entity_name' => $this->entity_name,
            'status' => $this->status,
            'comment' => $this->comment,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'type' => $this->type,
            'is_submitted' => $this->is_submitted,
            'is_active' => $this->is_active,
            'request_removed' => $this->request_removed,
            'date_created' => $this->date_created,
            'created_by' => $this->created_by,
        ];

        if (method_exists($this->resource, 'appendFilesToResource')) {
            return $this->resource->appendFilesToResource($data);
        }

        return $data;
    }
}
