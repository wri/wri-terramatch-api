<?php

namespace App\Http\Resources\V2\SiteReports;

use App\Http\Resources\V2\Forms\FormResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteReportWithSchemaResource extends JsonResource
{
    public function __construct($resource, $params)
    {
        parent::__construct($resource);
        $this->schema = data_get($params, 'schema', null);
    }

    public function toArray($request)
    {
        $params = [
            'model_uuid' => $this->uuid,
            'model' => $this,
        ];

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'form' => (new FormResource($this->schema))->params($params),
            'answers' => $this->getEntityAnswers($this->schema),
            'status' => $this->status,
        ];
    }
}
