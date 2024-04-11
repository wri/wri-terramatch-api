<?php

namespace App\Http\Resources\V2\Entities;

use App\Http\Resources\V2\Forms\FormResource;
use App\Models\V2\EntityModel;
use App\Models\V2\Forms\Form;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityWithSchemaResource extends JsonResource
{
    protected Form $schema;

    public function __construct(EntityModel $resource)
    {
        parent::__construct($resource);
        $this->schema = $resource->getForm();
    }

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
            'form' => (new FormResource($this->schema))->params($params),
            'answers' => $this->getEntityAnswers($this->schema),
            'status' => $this->status,
            'form_title' => $this->report_title ?? $this->title ?? $this->name,
            'update_request' => [
                'uuid' => $updateRequest?->uuid,
                'status' => $updateRequest?->status,
                'content' => $updateRequest?->content,
            ]
        ];
    }
}
