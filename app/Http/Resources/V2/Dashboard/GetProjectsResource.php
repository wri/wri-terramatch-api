<?php

namespace App\Http\Resources\V2\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class GetProjectsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource;
    }
}
