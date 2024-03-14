<?php

namespace App\Http\Resources\V2\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class ActiveProjectsTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items(),
            'per_page' => $this->perPage(),
            'total' => $this->total(),
        ];
    }
}
