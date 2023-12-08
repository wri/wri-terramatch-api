<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FormSectionCollection extends ResourceCollection
{
    protected $params;

    public function params(array $params = null)
    {
        $this->params = $params;

        return $this;
    }

    public function toArray($request)
    {
        return $this->collection->map(function (FormSectionResource $resource) use ($request) {
            return $resource
                ->params($this->params)
                ->toArray($request);
        })->all();
    }
}
