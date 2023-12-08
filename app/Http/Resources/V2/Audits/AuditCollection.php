<?php

namespace App\Http\Resources\V2\Audits;

use App\Http\Resources\V2\AuditResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AuditCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => AuditResource::collection($this->collection)];
    }
}
