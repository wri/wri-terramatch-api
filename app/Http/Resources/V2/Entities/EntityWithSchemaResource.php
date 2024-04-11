<?php

namespace App\Http\Resources\V2\Entities;

use App\Http\Resources\V2\Forms\FormResource;
use App\Models\V2\EntityModel;
use App\Models\V2\Forms\Form;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityWithSchemaResource extends JsonResource
{
    public function toArray($request)
    {
        $params = [
            'model_uuid' => $this->uuid,
            'model' => $this,
        ];

        $updateRequest = $this->updateRequests()->isUnapproved()->select('uuid', 'content')->first();
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'form' => (new FormResource($this->getForm()))->params($params),
            'answers' => $this->getEntityAnswers($this->getForm()),
            'status' => $this->status,
            'form_title' => $this->report_title ?? $this->title ?? $this->name,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'update_request' => $updateRequest == null ? null : [
                'uuid' => $updateRequest->uuid,
                'status' => $updateRequest->status,
                'content' => $updateRequest->content,
                'feedback' => $updateRequest->feedback,
                'feedback_fields' => $updateRequest->feedback_fields,
            ]
        ];
    }
}
