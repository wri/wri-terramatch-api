<?php

namespace App\Http\Resources\V2\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class ViewRestorationStrategyResource extends JsonResource
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
            'restorationStrategies' => $this->resource['restorationStrategies'] ?? null,
            'landUseTypes' => $this->resource['landUseTypes'] ?? null,
        ];
    }
}