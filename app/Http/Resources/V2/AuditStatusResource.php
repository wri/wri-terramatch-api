<?php

namespace App\Http\Resources\V2;

use App\Models\V2\User;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditStatusResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $isAuditStatus = isset($this->status);

        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'entity_name' => $this->entity_name ?? null,
            'status' => $isAuditStatus ? $this->status : ($this->new_values['status'] ?? $this->event),
            'comment' => $isAuditStatus ? $this->comment : ($this->new_values['feedback'] ?? null),
            'first_name' => $isAuditStatus ? $this->first_name : ($this->getUseFromAudit($this->user_id)->first_name ?? null),
            'last_name' => $isAuditStatus ? $this->last_name : ($this->getUseFromAudit($this->user_id)->last_name ?? null),
            'type' => $this->type ?? 'status',
            'is_submitted' => $this->is_submitted ?? null,
            'is_active' => $isAuditStatus ?? null,
            'request_removed' => $this->request_removed ?? null,
            'date_created' => $this->created_at,
            'created_by' => $isAuditStatus ? $this->created_by : ($this->user_id ?? null),
        ];

        if (method_exists($this->resource, 'appendFilesToResource')) {
            return $this->resource->appendFilesToResource($data);
        }

        return $data;
    }

    public function getUseFromAudit($user_id)
    {
        return User::where('id', $user_id)->first(['first_name', 'last_name']);
    }
}
