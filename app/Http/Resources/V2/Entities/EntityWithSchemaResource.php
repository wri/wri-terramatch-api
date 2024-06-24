<?php

namespace App\Http\Resources\V2\Entities;

use App\Http\Resources\V2\Forms\FormResource;
use App\Http\Resources\V2\UpdateRequests\UpdateRequestResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityWithSchemaResource extends JsonResource
{
    public function toArray($request)
    {
        $params = [
            'model_uuid' => $this->uuid,
            'model' => $this,
        ];

        $updateRequest = $this->updateRequests()->isUnapproved()->first();

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'form' => (new FormResource($this->getForm()))->params($params),
            'answers' => $this->getEntityAnswers($this->getForm()),
            'status' => $this->status,
            'form_title' => $this->report_title ?? $this->title ?? $this->name,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'update_request' => $updateRequest == null ? null : new UpdateRequestResource($updateRequest),
        ];
    }
}
